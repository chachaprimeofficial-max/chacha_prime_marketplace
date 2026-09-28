<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CommerceQrService
{
 public function product(int $productId,string $sku):string{return $this->make('product',$productId,$sku);}
 public function invoice(int $orderId,string $orderNumber):string{return $this->make('invoice',$orderId,$orderNumber);}
 private function make(string $type,int $id,string $ref):string{return url('/qr/'.$type.'/'.$id.'?ref='.rawurlencode($ref));}
 public function resolve(string $type,int $id){if($type==='product')return DB::table('products')->where('id',$id)->first();if($type==='invoice')return DB::table('orders')->where('id',$id)->first();abort(404);}
}
