<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletPaymentService
{
    public function debit(int $userId, float $amount, string $description): void
    {
        app(WalletService::class)->debit($userId, $amount, 'purchase', $description);
    }
}
