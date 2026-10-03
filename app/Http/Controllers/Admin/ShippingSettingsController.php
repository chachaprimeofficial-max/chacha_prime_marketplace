<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ShippingSettingsController extends Controller {
 public function index(){return view('admin.shipping-settings.index',['zones'=>DB::table('shipping_zones')->orderBy('name')->get(),'methods'=>DB::table('shipping_methods')->orderBy('name')->get(),'rates'=>DB::table('shipping_rates as r')->join('shipping_zones as z','z.id','=','r.shipping_zone_id')->join('shipping_methods as m','m.id','=','r.shipping_method_id')->select('r.*','z.name as zone_name','m.name as method_name')->latest('r.id')->get()]);}
 public function zone(Request $r){$d=$r->validate(['name'=>'required|string|max:120']);DB::table('shipping_zones')->insert(['name'=>$d['name'],'status'=>'active','created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Shipping zone created.');}
 public function method(Request $r){$d=$r->validate(['name'=>'required|string|max:120','carrier'=>'nullable|string|max:120','code'=>'required|string|max:80','description'=>'nullable|string|max:500']);DB::table('shipping_methods')->insert(array_merge($d,['status'=>'active','created_at'=>now(),'updated_at'=>now()]));return back()->with('success','Shipping method created.');}
 public function rate(Request $r){$d=$r->validate(['shipping_zone_id'=>'required|integer','shipping_method_id'=>'required|integer','rate'=>'required|numeric|min:0','free_shipping_min'=>'nullable|numeric|min:0','estimated_days_min'=>'nullable|integer|min:0','estimated_days_max'=>'nullable|integer|min:0']);DB::table('shipping_rates')->insert(array_merge($d,['created_at'=>now(),'updated_at'=>now()]));return back()->with('success','Shipping rate created.');}
}