<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = DB::table('wallets')->where('user_id', auth()->id())->first();
        $ledger = $wallet ? DB::table('wallet_ledger')->where('wallet_id', $wallet->id)->latest('id')->paginate(20) : collect();
        return view('customer.wallet', compact('wallet', 'ledger'));
    }
}
