<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index(Request $request){$returns=DB::table('returns')->where('user_id',$request->user()->id)->latest()->paginate(15);return view('customer.returns',compact('returns'));}
    public function create(Request $request,int $order){$orderRow=DB::table('orders')->where('id',$order)->where('user_id',$request->user()->id)->firstOrFail();$items=DB::table('order_items')->where('order_id',$order)->get();return view('customer.return-create',compact('orderRow','items'));}
    public function store(Request $request, ReturnService $returns){$data=$request->validate(['order_id'=>'required|integer','order_item_id'=>'required|integer','type'=>'required|in:refund,replacement','reason'=>'required|string|max:1000','shipping_label'=>'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240','media.*'=>'nullable|file|mimes:jpg,jpeg,png,mp4,mov,webm|max:51200']);$media=[];foreach($request->file('media',[]) as $file)$media[]=$file->store('returns/media','private');$label=$request->file('shipping_label')?->store('returns/labels','private');$id=$returns->create($request->user()->id,$data['order_id'],$data['order_item_id'],$data['type'],$data['reason'],$media,$label);return redirect()->route('customer.returns')->with('success','Return request #'.$id.' submitted.');}
    public function cancel(Request $request,int $return){$r=DB::table('returns')->where('id',$return)->where('user_id',$request->user()->id)->firstOrFail();abort_unless($r->status==='requested',422,'This return can no longer be cancelled.');DB::table('returns')->where('id',$return)->update(['status'=>'cancelled','updated_at'=>now()]);return back()->with('success','Return request cancelled.');}
}
