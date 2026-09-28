<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class TrackingController extends Controller
{
 public function show(int $order){$shipment=DB::table('shipments')->join('orders','orders.id','=','shipments.order_id')->where('shipments.order_id',$order)->where('orders.user_id',auth()->id())->select('shipments.*','orders.order_number')->latest('shipments.id')->firstOrFail();$events=DB::table('shipment_events')->where('shipment_id',$shipment->id)->orderByDesc('event_at')->get();return view('customer.shipping.track',compact('shipment','events'));}
}
