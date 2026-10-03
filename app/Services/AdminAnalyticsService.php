<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class AdminAnalyticsService
{
 public function overview():array{
  $paid=['paid','partially_refunded'];
  $revenue=(float)DB::table('payments')->whereIn('status',$paid)->sum('amount');
  $orders=DB::table('orders')->count();
  $customers=DB::table('customer_profiles')->count();
  $wallet=(float)DB::table('wallets')->sum('balance');
  $lowStock=DB::table('products')->where('status','active')->whereColumn('stock_qty','<=','low_stock_threshold')->count();
  $pendingOrders=DB::table('orders')->whereIn('status',['pending','confirmed','processing','packing'])->count();
  $pendingPayments=DB::table('payments')->where('status','pending')->count();
  $pendingReturns=DB::table('returns_requests')->whereIn('status',['pending','approved','received'])->count();
  $topProducts=DB::table('order_items')->join('products','products.id','=','order_items.product_id')->select('products.id','products.title',DB::raw('SUM(order_items.quantity) as units'),DB::raw('SUM(order_items.total_price) as sales'))->groupBy('products.id','products.title')->orderByDesc('units')->limit(8)->get();
  $recentOrders=DB::table('orders')->join('users','users.id','=','orders.user_id')->select('orders.id','orders.order_number','orders.total_amount','orders.status','payments.status','orders.created_at','users.name')->latest('orders.id')->limit(10)->get();
  $salesByDay=DB::table('orders')->whereIn('payment_status',$paid)->where('created_at','>=',now()->subDays(29)->startOfDay())->selectRaw('DATE(created_at) as day, SUM(total_amount) as revenue, COUNT(*) as orders')->groupByRaw('DATE(created_at)')->orderBy('day')->get();
  return compact('revenue','orders','customers','wallet','lowStock','pendingOrders','pendingPayments','pendingReturns','topProducts','recentOrders','salesByDay');
 }
}
