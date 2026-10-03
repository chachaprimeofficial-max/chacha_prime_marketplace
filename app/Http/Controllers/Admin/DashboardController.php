<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class DashboardController extends Controller
{
 public function index(): View
 {
  $stats=[
   'orders_today'=>DB::table('orders')->whereDate('created_at',today())->count(),
   'orders_total'=>DB::table('orders')->count(),
   'pending_orders'=>DB::table('orders')->whereIn('status',['pending','confirmed','packing','processing'])->count(),
   'shipped_orders'=>DB::table('orders')->whereIn('status',['shipped','in_transit','out_for_delivery'])->count(),
   'delivered_orders'=>DB::table('orders')->where('status','delivered')->count(),
   'products'=>DB::table('products')->count(),
   'active_products'=>DB::table('products')->where('status','active')->count(),
   'customers'=>DB::table('users')->whereDoesntHave('roles')->count(),
   'active_customers'=>DB::table('users')->whereDoesntHave('roles')->where('status','active')->count(),
   'open_returns'=>DB::table('returns')->whereIn('status',['requested','approved','received','inspected'])->count(),
   'pending_reviews'=>DB::table('reviews')->where('status','pending')->count(),
   'active_groups'=>DB::table('group_buying_campaigns')->where('status','active')->count(),
   'revenue_today'=>(float)DB::table('payments')->whereIn('status',['paid','partially_refunded'])->whereDate('created_at',today())->sum('amount'),
   'revenue_total'=>(float)DB::table('payments')->whereIn('status',['paid','partially_refunded'])->sum('amount'),
   'wallet_balance'=>(float)DB::table('wallets')->where('status','active')->sum('balance'),
  ];
  $recentOrders=DB::table('orders')->latest('id')->limit(10)->get();
  $lowStock=DB::table('products')->where('status','active')->whereColumn('stock_qty','<=','low_stock_threshold')->orderBy('stock_qty')->limit(8)->get();
  $recentCustomers=DB::table('users')->latest('id')->limit(6)->get();
  $pendingReviews=DB::table('reviews')->join('products','products.id','=','reviews.product_id')->join('users','users.id','=','reviews.user_id')->where('reviews.status','pending')->select('reviews.*','products.title as product_title','users.name as customer_name')->latest('reviews.id')->limit(6)->get();
  return view('admin.dashboard',compact('stats','recentOrders','lowStock','recentCustomers','pendingReviews'));
 }
}