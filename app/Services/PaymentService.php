<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    /**
     * Submit payment by customer or record payment by admin.
     */
    public function submitPayment(Order $order, User $customer, array $data, ?UploadedFile $proofFile = null): Payment
    {
        if ($order->customer_id !== $customer->id) {
            throw new Exception('Pembayaran hanya dapat dilakukan oleh pemilik pesanan.');
        }

        if (! $order->canAcceptPayment()) {
            throw new Exception('Pembayaran hanya dapat dilakukan ketika laundry berstatus Ready.');
        }

        if ($order->payments()->where('status', PaymentStatus::PENDING)->exists()) {
            throw new Exception('Pembayaran untuk pesanan ini sedang menunggu verifikasi admin.');
        }

        return DB::transaction(function () use ($order, $customer, $data, $proofFile) {
            $method = PaymentMethod::from($data['method']);
            $proofPath = null;

            if ($proofFile) {
                $proofPath = $proofFile->store('payments', 'public');
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => $method,
                'amount' => $order->total,
                'status' => PaymentStatus::PENDING,
                'proof_file' => $proofPath,
                'reference' => $data['reference'] ?? null,
                'paid_at' => null,
            ]);

            $order->update(['status' => OrderStatus::WAITING_PAYMENT]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::WAITING_PAYMENT,
                'note' => 'Pembayaran telah dikirim dan sedang menunggu verifikasi Admin.',
                'changed_by' => $customer->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($customer, "Mengirim pembayaran order {$order->order_number} via {$method->value}");

            return $payment;
        });
    }

    /**
     * Admin verifies and marks payment as PAID.
     */
    public function confirmPayment(Payment $payment, User $admin): Payment
    {
        if ($payment->status !== PaymentStatus::PENDING) {
            throw new Exception('Pembayaran ini sudah diproses sebelumnya.');
        }

        if ($payment->order->status !== OrderStatus::WAITING_PAYMENT) {
            throw new Exception('Order tidak sedang menunggu verifikasi pembayaran.');
        }

        return DB::transaction(function () use ($payment, $admin) {
            $payment->update([
                'status' => PaymentStatus::PAID,
                'paid_at' => now(),
                'verified_by' => $admin->id,
            ]);

            $order = $payment->order;
            $order->update([
                'payment_status' => PaymentStatus::PAID,
                'status' => OrderStatus::PAID,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::PAID,
                'note' => 'Pembayaran senilai Rp '.number_format($payment->amount, 0, ',', '.').' ('.$payment->method->label().') telah dikonfirmasi oleh Admin.',
                'changed_by' => $admin->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($admin, "Memverifikasi pembayaran order {$order->order_number}");

            return $payment;
        });
    }

    public function rejectPayment(Payment $payment, User $admin): Payment
    {
        if ($payment->status !== PaymentStatus::PENDING || $payment->order->status !== OrderStatus::WAITING_PAYMENT) {
            throw new Exception('Pembayaran ini tidak dapat ditolak pada status sekarang.');
        }

        return DB::transaction(function () use ($payment, $admin) {
            $payment->update(['status' => PaymentStatus::FAILED, 'verified_by' => $admin->id]);
            $order = $payment->order;
            $order->update(['status' => OrderStatus::READY, 'payment_status' => PaymentStatus::PENDING]);
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => OrderStatus::READY,
                'note' => 'Pembayaran ditolak Admin. Customer dapat mengirim pembayaran ulang.',
                'changed_by' => $admin->id,
                'created_at' => now(),
            ]);
            $this->activityLog->record($admin, "Menolak pembayaran order {$order->order_number}");

            return $payment;
        });
    }
}
