<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class DashboardController extends Controller
{
 public function index(): View
 {
  abort_unless(auth()->check() && in_array(strtolower((string)auth()->user()->email),array_map('strtolower',config('chacha.admin_emails',[])),true),403);
  $stats=[
   'orders_today'=>DB::table('orders')->whereDate('created_at',today())->count(),
   'orders_total'=>DB::table('orders')->count(),
   'pending_orders'=>DB::table('orders')->whereIn('status',['pending','confirmed','packing','processing'])->count(),
   'shipped_orders'=>DB::table('orders')->whereIn('status',['shipped'])->count(),
   'delivered_orders'=>DB::table('orders')->where('status','delivered')->count(),
   'products'=>DB::table('products')->count(),
   'active_products'=>DB::table('products')->where('status','active')->count(),
   'customers'=>DB::table('users')->where(function($q){$q->whereNull('role')->orWhereNotIn('role',['admin','staff']);})->count(),
   'active_customers'=>DB::table('users')->where(function($q){$q->whereNull('role')->orWhereNotIn('role',['admin','staff']);})->where('status','active')->count(),
   'open_returns'=>DB::table('returns_requests')->whereIn('status',['pending','approved','received'])->count(),
   'pending_reviews'=>DB::table('reviews')->where('status','pending')->count(),
   'active_groups'=>DB::table('group_buying_campaigns')->where('status','active')->count(),
   'revenue_today'=>(float)DB::table('orders')->whereDate('created_at',today())->whereExists(function($q){$q->select(DB::raw(1))->from('payments')->whereColumn('payments.order_id','orders.id')->where('payments.status','paid');})->sum('total_amount'),
   'revenue_total'=>(float)DB::table('orders')->whereExists(function($q){$q->select(DB::raw(1))->from('payments')->whereColumn('payments.order_id','orders.id')->where('payments.status','paid');})->sum('total_amount'),
   'wallet_balance'=>(float)DB::table('wallets')->where('status','active')->sum('balance'),
  ];
  $recentOrders=DB::table('orders')->latest('id')->limit(10)->get();
  $lowStock=DB::table('products')->where('status','active')->whereColumn('stock_qty','<=','low_stock_threshold')->orderBy('stock_qty')->limit(8)->get();
  $recentCustomers=DB::table('users')->where(function($q){$q->whereNull('role')->orWhereNotIn('role',['admin','staff']);})->latest('id')->limit(6)->get();
  $pendingReviews=DB::table('reviews')->join('products','products.id','=','product_reviews.product_id')->join('users','users.id','=','product_reviews.user_id')->where('product_reviews.status','pending')->select('product_reviews.*','products.title as product_title','users.name as customer_name')->latest('product_reviews.id')->limit(6)->get();
  return view('admin.dashboard',compact('stats','recentOrders','lowStock','recentCustomers','pendingReviews'));
 }
}
