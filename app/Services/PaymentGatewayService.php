<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentGatewayService
{
    public function recordPending(int $userId, ?int $orderId, float $amount, string $provider, string $currency = 'USD'): int
    {
        if ($amount <= 0) throw new RuntimeException('Payment amount must be positive.');
        return DB::table('payments')->insertGetId([
            'order_id' => $orderId,
            'user_id' => $userId,
            'provider' => $provider,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markPaid(int $paymentId, string $reference, array $providerPayload = []): void
    {
        DB::transaction(function () use ($paymentId, $reference, $providerPayload) {
            $payment = DB::table('payments')->where('id', $paymentId)->lockForUpdate()->first();
            if (!$payment) throw new RuntimeException('Payment not found.');
            if ($payment->status === 'paid') return;
            DB::table('payments')->where('id', $paymentId)->update([
                'transaction_reference' => $reference,
                'status' => 'paid',
                'provider_payload' => json_encode($providerPayload),
                'updated_at' => now(),
            ]);
            if ($payment->order_id) {
                DB::table('orders')->where('id', $payment->order_id)->update(['payment_status' => 'paid', 'status' => 'confirmed', 'updated_at' => now()]);
                DB::table('order_tracking_events')->insert(['order_id' => $payment->order_id, 'status' => 'confirmed', 'note' => 'Payment confirmed.', 'created_at' => now()]);
            }
        });
    }

    public function markFailed(int $paymentId, string $reason = ''): void
    {
        DB::table('payments')->where('id', $paymentId)->update([
            'status' => 'failed',
            'provider_payload' => json_encode(['reason' => $reason]),
            'updated_at' => now(),
        ]);
    }
}
