<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function recordPending(int $orderId, string $provider, string $method, float $amount, ?string $providerTransactionId = null): int
    {
        return DB::table('payments')->insertGetId([
            'order_id' => $orderId,
            'provider' => $provider,
            'method' => $method,
            'provider_transaction_id' => $providerTransactionId,
            'amount' => $amount,
            'currency' => config('chacha.brand.default_currency', 'USD'),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markPaid(int $paymentId, ?string $providerTransactionId = null): void
    {
        DB::transaction(function () use ($paymentId, $providerTransactionId) {
            $payment = DB::table('payments')->where('id', $paymentId)->lockForUpdate()->first();
            if (!$payment || $payment->status === 'paid') return;

            DB::table('payments')->where('id', $paymentId)->update([
                'status' => 'paid',
                'provider_transaction_id' => $providerTransactionId ?: $payment->provider_transaction_id,
                'paid_at' => now(),
                'updated_at' => now(),
            ]);

            if ($payment->order_id) {
                DB::table('orders')->where('id', $payment->order_id)->where('status', 'pending')
                    ->update(['status' => 'confirmed', 'updated_at' => now()]);
            }
        });
    }

    public function markFailed(int $paymentId, ?string $reason = null): void
    {
        DB::transaction(function () use ($paymentId, $reason) {
            $payment = DB::table('payments')->where('id', $paymentId)->lockForUpdate()->first();
            if (!$payment || in_array($payment->status, ['paid', 'refunded'], true)) return;

            DB::table('payments')->where('id', $paymentId)->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);

            if ($payment->order_id) {
                DB::table('orders')->where('id', $payment->order_id)->where('status', 'pending')
                    ->update(['status' => 'cancelled', 'updated_at' => now()]);
            }
        });
    }

    public function refund(int $paymentId, float $amount, ?string $reason = null): void
    {
        if ($amount <= 0) throw new RuntimeException('Invalid refund amount.');

        $refundId = DB::transaction(function () use ($paymentId, $amount, $reason) {
            $payment = DB::table('payments')->where('id', $paymentId)->lockForUpdate()->firstOrFail();
            if (!in_array($payment->status, ['paid', 'partially_refunded'], true)) {
                throw new RuntimeException('Only paid payments can be refunded.');
            }

            $refunded = (float) DB::table('payment_refunds')
                ->where('payment_id', $paymentId)->where('status', 'completed')->sum('amount');
            if ($refunded + $amount > (float) $payment->amount) {
                throw new RuntimeException('Refund exceeds payment amount.');
            }

            return DB::table('payment_refunds')->insertGetId([
                'payment_id' => $paymentId,
                'amount' => $amount,
                'reason' => $reason,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $refund = DB::table('payment_refunds as r')
            ->join('payments as p', 'p.id', '=', 'r.payment_id')
            ->where('r.id', $refundId)
            ->first(['r.*', 'p.provider', 'p.provider_transaction_id', 'p.currency']);

        try {
            $providerRefundId = null;
            if ($refund->provider === 'paypal') {
                $result = app(\App\Services\Payments\PayPalGateway::class)->refund((array) $refund, (float) $amount);
                $providerRefundId = $result['reference'] ?? null;
            }

            DB::transaction(function () use ($refundId, $paymentId, $providerRefundId) {
                $refundRow = DB::table('payment_refunds')->where('id', $refundId)->lockForUpdate()->firstOrFail();
                if ($refundRow->status === 'completed') return;

                DB::table('payment_refunds')->where('id', $refundId)->update([
                    'status' => 'completed',
                    'provider_refund_id' => $providerRefundId,
                    'updated_at' => now(),
                ]);

                $payment = DB::table('payments')->where('id', $paymentId)->lockForUpdate()->firstOrFail();
                $refunded = (float) DB::table('payment_refunds')
                    ->where('payment_id', $paymentId)->where('status', 'completed')->sum('amount');

                DB::table('payments')->where('id', $paymentId)->update([
                    'status' => $refunded >= (float) $payment->amount ? 'refunded' : 'partially_refunded',
                    'updated_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            DB::table('payment_refunds')->where('id', $refundId)->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);
            throw $e;
        }
    }
}
