<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ShipmentController extends Controller
{
 public function index(){return view('admin.shipments.index',['shipments'=>DB::table('shipments')->join('orders','orders.id','=','shipments.order_id')->select('shipments.*','orders.order_number')->latest('shipments.id')->paginate(30)]);}
 public function create(Request $r){$d=$r->validate(['order_id'=>'required|integer|exists:orders,id','carrier'=>'nullable|string|max:120','service'=>'nullable|string|max:120','tracking_number'=>'nullable|string|max:190','estimated_delivery_date'=>'nullable|date','notes'=>'nullable|string|max:2000']);app(ShipmentService::class)->create((int)$d['order_id'],$d);return back()->with('success','Shipment created.');}
 public function status(Request $r,int $shipment){$d=$r->validate(['status'=>'required|string','location'=>'nullable|string|max:190','message'=>'nullable|string|max:500']);app(ShipmentService::class)->updateStatus($shipment,$d['status'],$d['location']??null,$d['message']??null);return back()->with('success','Shipment status updated.');}
}
