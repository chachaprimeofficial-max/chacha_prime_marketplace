<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class CouponService
{
 public function findValid(string $code,float $subtotal){$coupon=DB::table('coupons')->whereRaw('UPPER(code)=?', [strtoupper(trim($code))])->where('status','active')->lockForUpdate()->first();if(!$coupon)throw new RuntimeException('Coupon is not valid.');if($coupon->starts_at&&now()->lt($coupon->starts_at))throw new RuntimeException('Coupon is not active yet.');if($coupon->expires_at&&now()->gt($coupon->expires_at))throw new RuntimeException('Coupon has expired.');if($coupon->max_uses!==null&&(int)$coupon->used_count>=(int)$coupon->max_uses)throw new RuntimeException('Coupon usage limit has been reached.');if($subtotal<(float)$coupon->min_order_amount)throw new RuntimeException('Minimum order amount for this coupon has not been reached.');return $coupon;}
 public function discount(object $coupon,float $subtotal):float{return round($coupon->type==='percent'?min($subtotal,$subtotal*((float)$coupon->value/100)):min($subtotal,(float)$coupon->value),2);}
 public function consume(int $couponId):void{DB::table('coupons')->where('id',$couponId)->increment('used_count');}
}
