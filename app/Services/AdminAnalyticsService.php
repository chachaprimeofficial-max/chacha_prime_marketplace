<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class AdminAnalyticsService
{
 public function overview():array{
  $paid=['paid','partially_refunded'];
  $revenue=(float)DB::table('orders')->whereIn('payment_status',$paid)->sum('total_amount');
  $orders=DB::table('orders')->count();
  $customers=DB::table('users')->where('role','customer')->count();
  $refunds=(float)DB::table('payment_refunds')->whereIn('status',['pending','processed'])->sum('amount');
  $wallet=(float)DB::table('wallets')->sum('balance');
  $lowStock=DB::table('products')->where('status','active')->whereColumn('stock_quantity','<=','low_stock_threshold')->count();
  $pendingOrders=DB::table('orders')->whereIn('status',['pending','confirmed','processing'])->count();
  $pendingPayments=DB::table('payments')->where('status','pending')->count();
  $pendingReturns=DB::table('returns')->whereIn('status',['requested','approved','received'])->count();
  $topProducts=DB::table('order_items')->join('products','products.id','=','order_items.product_id')->select('products.id','products.title',DB::raw('SUM(order_items.quantity) as units'),DB::raw('SUM(order_items.total_price) as sales'))->groupBy('products.id','products.title')->orderByDesc('units')->limit(8)->get();
  $recentOrders=DB::table('orders')->join('users','users.id','=','orders.user_id')->select('orders.id','orders.order_number','orders.total_amount','orders.status','orders.payment_status','orders.created_at','users.name')->latest('orders.id')->limit(10)->get();
  return compact('revenue','orders','customers','refunds','wallet','lowStock','pendingOrders','pendingPayments','pendingReturns','topProducts','recentOrders');
 }
}
