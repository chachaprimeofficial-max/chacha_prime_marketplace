<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PaymentMethodController extends Controller
{
 public function index(){return view('admin.payments.methods',['methods'=>DB::table('payment_methods')->orderBy('sort_order')->orderBy('name')->get()]);}
 public function store(Request $request){$data=$request->validate(['name'=>'required|string|max:100','provider'=>'required|string|max:100','enabled'=>'nullable|boolean','sort_order'=>'nullable|integer|min:0','config_json'=>'nullable|array']);DB::table('payment_methods')->insert(['name'=>$data['name'],'provider'=>$data['provider'],'enabled'=>$data['enabled']??true,'sort_order'=>$data['sort_order']??0,'config_json'=>isset($data['config_json'])?json_encode($data['config_json']):null,'created_at'=>now(),'updated_at'=>now()]);return back()->with('success','Payment method added.');}
 public function update(Request $request,int $method){$data=$request->validate(['name'=>'required|string|max:100','provider'=>'required|string|max:100','enabled'=>'nullable|boolean','sort_order'=>'nullable|integer|min:0','config_json'=>'nullable|array']);DB::table('payment_methods')->where('id',$method)->update(['name'=>$data['name'],'provider'=>$data['provider'],'enabled'=>$data['enabled']??false,'sort_order'=>$data['sort_order']??0,'config_json'=>isset($data['config_json'])?json_encode($data['config_json']):null,'updated_at'=>now()]);return back()->with('success','Payment method configuration updated.');}
 public function toggle(int $method){$m=DB::table('payment_methods')->where('id',$method)->firstOrFail();DB::table('payment_methods')->where('id',$method)->update(['enabled'=>!$m->enabled,'updated_at'=>now()]);return back()->with('success','Payment method updated.');}
}
