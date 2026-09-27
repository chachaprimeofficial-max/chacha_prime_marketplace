<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function credit(int $userId, float $amount, string $reason, ?string $description = null): void
    {
        $this->entry($userId, 'credit', $amount, $reason, $description);
    }

    public function debit(int $userId, float $amount, string $reason, ?string $description = null): void
    {
        $this->entry($userId, 'debit', $amount, $reason, $description);
    }

    private function entry(int $userId, string $type, float $amount, string $reason, ?string $description): void
    {
        if ($amount <= 0) throw new RuntimeException('Wallet amount must be greater than zero.');
        DB::transaction(function () use ($userId, $type, $amount, $reason, $description) {
            $wallet = DB::table('wallets')->where('user_id', $userId)->lockForUpdate()->first();
            if (!$wallet) {
                DB::table('wallets')->insert(['user_id'=>$userId,'currency'=>config('chacha.brand.default_currency','USD'),'balance'=>0,'status'=>'active','created_at'=>now(),'updated_at'=>now()]);
                $wallet = DB::table('wallets')->where('user_id', $userId)->lockForUpdate()->first();
            }
            $balance = $type === 'credit' ? (float)$wallet->balance + $amount : (float)$wallet->balance - $amount;
            if ($balance < 0) throw new RuntimeException('Insufficient wallet balance.');
            DB::table('wallets')->where('id',$wallet->id)->update(['balance'=>$balance,'updated_at'=>now()]);
            DB::table('wallet_ledger')->insert(['wallet_id'=>$wallet->id,'entry_type'=>$type,'reason'=>$reason,'amount'=>$amount,'balance_after'=>$balance,'description'=>$description,'created_at'=>now()]);
        });
    }
}
