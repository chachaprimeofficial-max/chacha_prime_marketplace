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
  $pendingReturns=DB::table('returns')->whereIn('status',['requested','approved','received','inspected'])->count();
  $topProducts=DB::table('order_items')->join('products','products.id','=','order_items.product_id')->select('products.id','products.title',DB::raw('SUM(order_items.quantity) as units'),DB::raw('SUM(order_items.total_price) as sales'))->groupBy('products.id','products.title')->orderByDesc('units')->limit(8)->get();
  $recentOrders=DB::table('orders')->join('users','users.id','=','orders.user_id')->leftJoin('payments','payments.order_id','=','orders.id')->select('orders.id','orders.order_number','orders.total_amount','orders.status','payments.status as payment_status','orders.created_at','users.name')->latest('orders.id')->limit(10)->get();
  $salesByDay=DB::table('orders')->join('payments','payments.order_id','=','orders.id')->whereIn('payments.status',$paid)->where('orders.created_at','>=',now()->subDays(29)->startOfDay())->selectRaw('DATE(orders.created_at) as day, SUM(orders.total_amount) as revenue, COUNT(DISTINCT orders.id) as orders')->groupByRaw('DATE(orders.created_at)')->orderBy('day')->get();
  return compact('revenue','orders','customers','wallet','lowStock','pendingOrders','pendingPayments','pendingReturns','topProducts','recentOrders','salesByDay');
 }
}