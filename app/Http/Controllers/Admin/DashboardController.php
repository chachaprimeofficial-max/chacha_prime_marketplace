<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'orders_today' => DB::table('orders')->whereDate('created_at', today())->count(),
            'pending_orders' => DB::table('orders')->whereIn('status', ['pending','confirmed','packing'])->count(),
            'products' => DB::table('products')->count(),
            'customers' => DB::table('users')->count(),
            'open_returns' => DB::table('returns')->whereIn('status', ['requested','approved','in_transit'])->count(),
            'active_groups' => DB::table('group_buying_campaigns')->where('status','active')->count(),
            'revenue_today' => DB::table('orders')->whereDate('created_at', today())->whereNotIn('status',['cancelled','refunded'])->sum('total_amount'),
        ];
        $recentOrders = DB::table('orders')->latest('id')->limit(10)->get();
        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}
