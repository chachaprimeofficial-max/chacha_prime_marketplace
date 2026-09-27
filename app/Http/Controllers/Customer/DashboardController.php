<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $orders = DB::table('orders')->where('user_id', $userId)->latest('id')->limit(5)->get();
        $wallet = DB::table('wallets')->where('user_id', $userId)->first();
        $openReturns = DB::table('returns')->where('user_id', $userId)->whereIn('status', ['requested', 'approved', 'in_transit'])->count();
        return view('customer.dashboard', compact('orders', 'wallet', 'openReturns'));
    }
}
