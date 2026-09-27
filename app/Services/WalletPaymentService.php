<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletPaymentService
{
    public function debit(int $userId, float $amount, string $description): void
    {
        if ($amount <= 0) throw new RuntimeException('Invalid wallet amount.');

        DB::transaction(function () use ($userId, $amount, $description) {
            $wallet = DB::table('wallets')->where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet || $wallet->status !== 'active' || (float) $wallet->balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }
            $balance = (float) $wallet->balance - $amount;
            DB::table('wallets')->where('id', $wallet->id)->update(['balance' => $balance, 'updated_at' => now()]);
            DB::table('wallet_ledger')->insert([
                'wallet_id' => $wallet->id,
                'entry_type' => 'debit',
                'reason' => 'purchase',
                'amount' => $amount,
                'balance_after' => $balance,
                'description' => $description,
                'created_at' => now(),
            ]);
        });
    }
}
