<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ShipmentController extends Controller
{
 public function index(){return view('admin.shipments.index',['shipments'=>DB::table('shipments')->join('orders','orders.id','=','shipments.order_id')->select('shipments.*','orders.order_number')->latest('shipments.id')->paginate(30)]);}
 public function create(Request $r){$d=$r->validate(['order_id'=>'required|integer|exists:orders,id','shipping_method_id'=>'nullable|integer|exists:shipping_methods,id','carrier'=>'nullable|string|max:120','tracking_number'=>'nullable|string|max:190','tracking_url'=>'nullable|url|max:500','notes'=>'nullable|string|max:1000']);app(ShipmentService::class)->create((int)$d['order_id'],$d);return back()->with('success','Shipment created.');}
 public function status(Request $r,int $shipment){$d=$r->validate(['status'=>'required|in:pending,label_created,picked_up,in_transit,out_for_delivery,delivered,exception,returned','location'=>'nullable|string|max:190','message'=>'nullable|string|max:500']);app(ShipmentService::class)->updateStatus($shipment,$d['status'],$d['location']??null,$d['message']??null);return back()->with('success','Shipment status updated.');}
}
