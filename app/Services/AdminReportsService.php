<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class AdminReportsService
{
 public function summary(?string $from=null,?string $to=null):array{
  $from=$from?:now()->startOfMonth()->toDateString();$to=$to?:now()->toDateString();
  $orders=DB::table('orders')->whereBetween('created_at',[$from.' 00:00:00',$to.' 23:59:59']);
  $revenue=(float)DB::table('payments')->whereBetween('created_at',[$from.' 00:00:00',$to.' 23:59:59'])->whereIn('status',['paid','partially_refunded'])->sum('amount');
  $orderCount=(clone $orders)->count();
  $refunds=(float)DB::table('payment_refunds')->whereBetween('created_at',[$from.' 00:00:00',$to.' 23:59:59'])->sum('amount');
  $tax=(float)(clone $orders)->sum('tax_amount');
  $shipping=(float)(clone $orders)->sum('shipping_amount');
  $byPayment=DB::table('payments')->whereBetween('created_at',[$from.' 00:00:00',$to.' 23:59:59'])->select('provider',DB::raw('COUNT(*) as transactions'),DB::raw('SUM(CASE WHEN status="paid" THEN amount ELSE 0 END) as paid_amount'))->groupBy('provider')->orderByDesc('paid_amount')->get();
  $byDay=DB::table('orders')->whereBetween('created_at',[$from.' 00:00:00',$to.' 23:59:59'])->select(DB::raw('DATE(created_at) as day'),DB::raw('COUNT(*) as orders'),DB::raw('SUM(total_amount) as revenue'))->groupBy(DB::raw('DATE(created_at)'))->orderBy('day')->get();
  $categories=DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->join('products','products.id','=','order_items.product_id')->leftJoin('categories','categories.id','=','products.category_id')->whereBetween('orders.created_at',[$from.' 00:00:00',$to.' 23:59:59'])->select('categories.name',DB::raw('SUM(order_items.quantity) as units'),DB::raw('SUM(order_items.total_price) as sales'))->groupBy('categories.id','categories.name')->orderByDesc('sales')->get();
  return compact('from','to','revenue','orderCount','refunds','tax','shipping','byPayment','byDay','categories');
 }
}
