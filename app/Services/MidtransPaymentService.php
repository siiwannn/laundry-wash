<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Midtrans\Config;
use Midtrans\Snap;
use RuntimeException;

class MidtransPaymentService
{
    public function createSnapToken(Order $order, Payment $payment): string
    {
        $this->configure();

        $payload = [
            'transaction_details' => [
                'order_id' => $payment->gateway_order_id,
                'gross_amount' => (int) round((float) $payment->amount),
            ],
            'customer_details' => [
                'first_name' => $order->customer->name,
                'email' => $order->customer->email,
                'phone' => $order->customer->phone,
            ],
            'item_details' => [[
                'id' => $order->order_number,
                'price' => (int) round((float) $payment->amount),
                'quantity' => 1,
                'name' => 'Laundry '.$order->order_number,
            ]],
            'enabled_payments' => ['other_qris', 'bca_va', 'bni_va', 'bri_va', 'permata_va'],
            'expiry' => ['unit' => 'hours', 'duration' => 24],
        ];

        if (app()->environment(['local', 'development'])) {
            Log::debug('Midtrans Snap payload', ['payload' => $payload]);
        }

        $token = Snap::getSnapToken($payload);

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Midtrans tidak mengembalikan Snap token yang valid.');
        }

        return $token;
    }

    public function hasValidSignature(array $notification): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            return false;
        }

        $expected = hash('sha512',
            $notification['order_id'].
            $notification['status_code'].
            $notification['gross_amount'].
            $serverKey
        );

        return hash_equals($expected, $notification['signature_key']);
    }

    public function resolveMethod(?string $paymentType): PaymentMethod
    {
        return match ($paymentType) {
            'qris' => PaymentMethod::QRIS,
            'bank_transfer', 'echannel', 'permata' => PaymentMethod::VIRTUAL_ACCOUNT,
            default => throw new InvalidArgumentException('Metode pembayaran Midtrans tidak didukung.'),
        };
    }

    private function configure(): void
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }
}
