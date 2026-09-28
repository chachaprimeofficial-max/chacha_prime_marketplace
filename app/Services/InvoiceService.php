<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class InvoiceService
{
 public function createForOrder(int $orderId):int{return DB::transaction(function()use($orderId){$order=DB::table('orders')->where('id',$orderId)->lockForUpdate()->firstOrFail();$existing=DB::table('invoices')->where('order_id',$orderId)->value('id');if($existing)return (int)$existing;$invoiceNumber='CP-INV-'.now()->format('Ym').'-'.Str::upper(Str::random(8));$tax=(float)($order->tax_amount??0);$id=DB::table('invoices')->insertGetId(['order_id'=>$orderId,'invoice_number'=>$invoiceNumber,'subtotal'=>$order->subtotal,'tax_amount'=>$tax,'shipping_amount'=>$order->shipping_amount,'discount_amount'=>$order->discount_amount,'total_amount'=>$order->total_amount,'currency'=>$order->currency,'billing_address'=>$order->billing_address??null,'shipping_address'=>$order->shipping_address,'payment_method'=>$order->payment_method,'qr_payload'=>route('customer.invoices.show',$orderId),'issued_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);$items=DB::table('order_items')->where('order_id',$orderId)->get();foreach($items as $item)DB::table('invoice_items')->insert(['invoice_id'=>$id,'product_id'=>$item->product_id,'title'=>$item->title,'sku'=>$item->sku,'quantity'=>$item->quantity,'unit_price'=>$item->unit_price,'tax_amount'=>0,'total_amount'=>$item->total_price,'created_at'=>now(),'updated_at'=>now()]);return $id;});}
 public function data(int $orderId):array{$order=DB::table('orders')->where('id',$orderId)->firstOrFail();$invoice=DB::table('invoices')->where('order_id',$orderId)->firstOrFail();$items=DB::table('invoice_items')->where('invoice_id',$invoice->id)->get();return ['order'=>$order,'invoice'=>$invoice,'items'=>$items,'qr_payload'=>$invoice->qr_payload];}
}
