<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->check() && in_array(strtolower((string) auth()->user()->email), array_map('strtolower', config('chacha.admin_emails', [])), true), 403);

        $stats = [
            'orders_today' => DB::table('orders')->whereDate('created_at', today())->count(),
            'pending_orders' => DB::table('orders')->whereIn('status', ['pending','confirmed','packing','processing'])->count(),
            'products' => DB::table('products')->count(),
            'active_products' => DB::table('products')->where('status', 'active')->count(),
            'customers' => DB::table('users')->where('status', 'active')->count(),
            'open_returns' => DB::table('returns')->whereIn('status', ['requested','approved','in_transit','received'])->count(),
            'active_groups' => DB::table('group_buying_campaigns')->where('status','active')->count(),
            'revenue_today' => (float) DB::table('orders')->whereDate('created_at', today())->where('payment_status','paid')->sum('total_amount'),
            'revenue_total' => (float) DB::table('orders')->where('payment_status','paid')->sum('total_amount'),
            'wallet_balance' => (float) DB::table('wallets')->where('status','active')->sum('balance'),
        ];

        $recentOrders = DB::table('orders')->latest('id')->limit(10)->get();
        $lowStock = DB::table('products')->where('status','active')->whereColumn('stock_qty','<=','low_stock_threshold')->orderBy('stock_qty')->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'lowStock'));
    }
}
