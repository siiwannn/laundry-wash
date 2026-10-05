<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PaymentService
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
        private readonly MidtransPaymentService $midtrans,
    ) {}

    public function createMidtransPayment(Order $order, User $customer, bool $refreshToken = false): Payment
    {
        if ($order->customer_id !== $customer->id) {
            throw new Exception('Pembayaran hanya dapat dilakukan oleh pemilik pesanan.');
        }

        if (! $order->canAcceptPayment()) {
            throw new Exception('Pembayaran hanya dapat dilakukan ketika laundry berstatus Ready.');
        }

        $activePayment = $order->payments()
            ->where('status', PaymentStatus::PENDING)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($activePayment?->snap_token && ! $refreshToken) {
            return $activePayment;
        }

        if ($activePayment?->snap_token && $refreshToken) {
            return DB::transaction(function () use ($activePayment, $order, $customer) {
                $snapToken = $this->midtrans->createSnapToken($order->loadMissing('customer'), $activePayment);
                $activePayment->update(['snap_token' => $snapToken]);
                $this->activityLog->record($customer, "Payment Snap Token Refreshed: {$activePayment->gateway_order_id}");

                return $activePayment->fresh();
            });
        }

        return DB::transaction(function () use ($order, $customer) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'gateway_order_id' => 'LW-'.$order->id.'-'.Str::uuid(),
                'amount' => $order->total,
                'status' => PaymentStatus::PENDING,
                'gateway_status' => 'pending',
                'expires_at' => now()->addDay(),
            ]);

            $this->activityLog->record($customer, "Payment Created: {$payment->gateway_order_id}");
            $snapToken = $this->midtrans->createSnapToken($order->loadMissing('customer'), $payment);
            $payment->update(['snap_token' => $snapToken]);
            $order->update(['status' => OrderStatus::WAITING_PAYMENT]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::WAITING_PAYMENT,
                'note' => 'Transaksi Midtrans dibuat dan menunggu pembayaran customer.',
                'changed_by' => $customer->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($customer, "Payment Pending: {$payment->gateway_order_id}");

            return $payment->fresh();
        });
    }

    public function handleMidtransNotification(array $notification): Payment
    {
        if (! $this->midtrans->hasValidSignature($notification)) {
            throw new AccessDeniedHttpException('Signature webhook Midtrans tidak valid.');
        }

        return DB::transaction(function () use ($notification) {
            $payment = Payment::where('gateway_order_id', $notification['order_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (abs((float) $payment->amount - (float) $notification['gross_amount']) > 0.01) {
                throw new UnprocessableEntityHttpException('Nominal webhook tidak sesuai dengan tagihan.');
            }

            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'] ?? null;
            $previousGatewayStatus = $payment->gateway_status;
            $method = $this->midtrans->resolveMethod($notification['payment_type'] ?? null);

            $payment->update([
                'method' => $method,
                'gateway_transaction_id' => $notification['transaction_id'] ?? $payment->gateway_transaction_id,
                'gateway_status' => $transactionStatus,
            ]);

            if (in_array($transactionStatus, ['capture', 'settlement'], true)
                && ($transactionStatus !== 'capture' || in_array($fraudStatus, [null, 'accept'], true))) {
                return $this->markPaid($payment);
            }

            if ($transactionStatus === 'pending') {
                if ($previousGatewayStatus !== 'pending') {
                    $this->activityLog->record(null, "Payment Pending: {$payment->gateway_order_id}");
                }

                return $payment;
            }

            if ($transactionStatus === 'expire') {
                return $this->markFailed($payment, 'Payment Expired');
            }

            if (in_array($transactionStatus, ['deny', 'cancel', 'failure'], true)) {
                return $this->markFailed($payment, 'Payment Failed');
            }

            return $payment;
        });
    }

    private function markPaid(Payment $payment): Payment
    {
        if ($payment->status === PaymentStatus::PAID) {
            return $payment;
        }

        $payment->update(['status' => PaymentStatus::PAID, 'paid_at' => now()]);
        $order = $payment->order;
        $order->update(['payment_status' => PaymentStatus::PAID, 'status' => OrderStatus::PAID]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => OrderStatus::PAID,
            'note' => 'Pembayaran dikonfirmasi otomatis oleh webhook Midtrans.',
            'changed_by' => null,
            'created_at' => now(),
        ]);
        $this->activityLog->record(null, "Payment Paid: {$payment->gateway_order_id}");

        return $payment;
    }

    private function markFailed(Payment $payment, string $activity): Payment
    {
        if ($payment->status === PaymentStatus::FAILED && $payment->order->status === OrderStatus::READY) {
            return $payment;
        }

        $payment->update(['status' => PaymentStatus::FAILED]);
        $order = $payment->order;
        $order->update(['status' => OrderStatus::READY, 'payment_status' => PaymentStatus::PENDING]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => OrderStatus::READY,
            'note' => $activity === 'Payment Expired'
                ? 'Transaksi Midtrans kedaluwarsa. Customer dapat membuat pembayaran baru.'
                : 'Pembayaran Midtrans gagal. Customer dapat mencoba kembali.',
            'changed_by' => null,
            'created_at' => now(),
        ]);
        $this->activityLog->record(null, "{$activity}: {$payment->gateway_order_id}");

        return $payment;
    }
}
