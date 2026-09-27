<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(): View
    {
        $userId = (int) auth()->id();

        $wallet = DB::table('wallets')->where('user_id', $userId)->first();
        $ledger = $wallet
            ? DB::table('wallet_ledger')->where('wallet_id', $wallet->id)->latest('id')->paginate(20)
            : collect();

        return view('customer.wallet', [
            'wallet' => $wallet,
            'ledger' => $ledger,
        ]);
    }
}
