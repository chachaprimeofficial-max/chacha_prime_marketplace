<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class AdminProfitReportService
{
 public function report(?string $from=null,?string $to=null):array{
  $from=$from?:now()->startOfMonth()->toDateString();$to=$to?:now()->toDateString();
  $rows=DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->join('products','products.id','=','order_items.product_id')->whereBetween('orders.created_at',[$from.' 00:00:00',$to.' 23:59:59'])->whereExists(function($q){$q->select(DB::raw(1))->from('payments')->whereColumn('payments.order_id','orders.id')->whereIn('payments.status',['paid','partially_refunded']);})->select('products.id','products.title','products.cost_price',DB::raw('SUM(order_items.quantity) units'),DB::raw('SUM(order_items.total_price) revenue'),DB::raw('SUM(order_items.quantity * COALESCE(products.cost_price,0)) cost'))->groupBy('products.id','products.title','products.cost_price')->orderByDesc('revenue')->get()->map(function($r){$r->gross_profit=(float)$r->revenue-(float)$r->cost;$r->margin_percent=(float)$r->revenue>0?round(($r->gross_profit/(float)$r->revenue)*100,2):0;return $r;});
  return ['from'=>$from,'to'=>$to,'rows'=>$rows,'revenue'=>(float)$rows->sum('revenue'),'cost'=>(float)$rows->sum('cost'),'gross_profit'=>(float)$rows->sum('gross_profit')];
 }
}
