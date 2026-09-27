<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordPending(int $orderId, int $userId, string $provider, float $amount, ?string $transactionReference = null): int
    {
        return DB::table('payments')->insertGetId([
            'order_id' => $orderId,
            'user_id' => $userId,
            'provider' => $provider,
            'transaction_reference' => $transactionReference,
            'amount' => $amount,
            'currency' => config('chacha.brand.default_currency', 'USD'),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markPaid(int $paymentId, ?string $transactionReference = null): void
    {
        DB::transaction(function () use ($paymentId, $transactionReference) {
            $payment = DB::table('payments')->where('id', $paymentId)->lockForUpdate()->first();
            if (!$payment) return;

            DB::table('payments')->where('id', $paymentId)->update([
                'status' => 'paid',
                'transaction_reference' => $transactionReference ?: $payment->transaction_reference,
                'updated_at' => now(),
            ]);

            if ($payment->order_id) {
                DB::table('orders')->where('id', $payment->order_id)->update([
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
