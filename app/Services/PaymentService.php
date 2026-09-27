<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordPending(int $orderId, string $provider, string $method, float $amount): int
    {
        return DB::table('payments')->insertGetId([
            'order_id' => $orderId,
            'provider' => $provider,
            'method' => $method,
            'amount' => $amount,
            'currency' => config('chacha.brand.default_currency', 'USD'),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markPaid(int $paymentId, ?string $transactionId = null): void
    {
        DB::transaction(function () use ($paymentId, $transactionId) {
            $payment = DB::table('payments')->where('id', $paymentId)->lockForUpdate()->first();
            if (!$payment) return;
            DB::table('payments')->where('id', $paymentId)->update([
                'status' => 'paid',
                'provider_transaction_id' => $transactionId,
                'paid_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('orders')->where('id', $payment->order_id)->update(['status' => 'confirmed', 'updated_at' => now()]);
        });
    }
}
