<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ShipmentTrackingController extends Controller
{
 public function show(Request $request,int $order){$o=DB::table('orders')->where('id',$order)->where('user_id',$request->user()->id)->firstOrFail();$shipments=DB::table('shipments')->where('order_id',$o->id)->latest('id')->get();$events=DB::table('shipment_events as e')->join('shipments as s','s.id','=','e.shipment_id')->where('s.order_id',$o->id)->orderByDesc('e.event_at')->get(['e.*','s.tracking_number','s.carrier']);return view('customer.shipment-tracking',compact('o','shipments','events'));}
}
