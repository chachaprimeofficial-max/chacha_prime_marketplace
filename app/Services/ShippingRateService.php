<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use RuntimeException;
class ShippingRateService
{
 public function quote(string $countryCode,float $subtotal): array
 {
  $zone=DB::table('shipping_zones')->join('shipping_zone_countries','shipping_zone_countries.shipping_zone_id','=','shipping_zones.id')->where('shipping_zones.status','active')->where('shipping_zone_countries.country_code',strtoupper($countryCode))->select('shipping_zones.id')->first();
  if(!$zone)throw new RuntimeException('Shipping is not available to this country.');
  $rates=DB::table('shipping_rates')->join('shipping_methods','shipping_methods.id','=','shipping_rates.shipping_method_id')->where('shipping_rates.shipping_zone_id',$zone->id)->where('shipping_methods.status','active')->orderBy('shipping_rates.rate')->get();
  return $rates->map(fn($r)=>['id'=>$r->id,'method_id'=>$r->shipping_method_id,'name'=>$r->name,'carrier'=>$r->carrier,'rate'=>($r->free_shipping_min!==null&&$subtotal>=$r->free_shipping_min)?0:(float)$r->rate,'estimated_days_min'=>$r->estimated_days_min,'estimated_days_max'=>$r->estimated_days_max])->values()->all();
 }
 public function calculate(string $countryCode,float $subtotal,int $rateId): array
 {
  $rate=DB::table('shipping_rates')->join('shipping_methods','shipping_methods.id','=','shipping_rates.shipping_method_id')->join('shipping_zones','shipping_zones.id','=','shipping_rates.shipping_zone_id')->join('shipping_zone_countries','shipping_zone_countries.shipping_zone_id','=','shipping_zones.id')->where('shipping_rates.id',$rateId)->where('shipping_methods.status','active')->where('shipping_zones.status','active')->where('shipping_zone_countries.country_code',strtoupper($countryCode))->select('shipping_rates.*','shipping_methods.name','shipping_methods.carrier')->first();
  if(!$rate)throw new RuntimeException('Selected shipping method is not available for this destination.');
  return ['rate_id'=>$rate->id,'method_id'=>$rate->shipping_method_id,'name'=>$rate->name,'carrier'=>$rate->carrier,'amount'=>($rate->free_shipping_min!==null&&$subtotal>=$rate->free_shipping_min)?0:(float)$rate->rate,'estimated_days_min'=>$rate->estimated_days_min,'estimated_days_max'=>$rate->estimated_days_max];
 }
}
