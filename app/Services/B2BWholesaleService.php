<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class B2BWholesaleService {
 public function isVerified(int $userId):bool{return (bool)DB::table('business_profiles')->where('user_id',$userId)->where('status','verified')->exists();}
 public function price(int $userId,int $productId,?int $variantId=null,float $quantity=1):float{$p=DB::table('products')->where('id',$productId)->firstOrFail();if(!$this->isVerified($userId))throw new RuntimeException('Verified B2B business account required.');$price=$variantId?DB::table('product_variants')->where('id',$variantId)->where('product_id',$productId)->value('wholesale_price'):$p->wholesale_price;if($price===null)throw new RuntimeException('Wholesale price is not configured for this product.');return (float)$price;}
 public function validateOrder(int $userId,array $items):void{if(!$this->isVerified($userId))throw new RuntimeException('Verified B2B business account required.');foreach($items as $item){if((int)$item['quantity']<1)throw new RuntimeException('Invalid wholesale quantity.');$this->price($userId,(int)$item['product_id'],isset($item['variant_id'])?(int)$item['variant_id']:null,(float)$item['quantity']);}}
}