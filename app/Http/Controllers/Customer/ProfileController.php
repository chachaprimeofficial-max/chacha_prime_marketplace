<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ProfileController extends Controller
{
 public function profile(Request $r){return view('customer.profile',['user'=>$r->user()]);}
 public function update(Request $r){$d=$r->validate(['name'=>'required|string|max:120','phone'=>'nullable|string|max:40']);$r->user()->update($d);return back()->with('success','Profile updated.');}
 public function addresses(Request $r){$addresses=DB::table('addresses')->where('user_id',$r->user()->id)->latest()->get();return view('customer.addresses',compact('addresses'));}
 public function addressStore(Request $r){$d=$r->validate(['label'=>'required|string|max:60','full_name'=>'required|string|max:120','phone'=>'required|string|max:40','address_line_1'=>'required|string|max:255','address_line_2'=>'nullable|string|max:255','city'=>'required|string|max:100','state'=>'nullable|string|max:100','postal_code'=>'nullable|string|max:30','country'=>'required|string|max:100','is_default'=>'nullable|boolean']);$uid=$r->user()->id;if(!empty($d['is_default']))DB::table('addresses')->where('user_id',$uid)->update(['is_default'=>0]);DB::table('addresses')->insert(array_merge($d,['user_id'=>$uid,'is_default'=>(int)($d['is_default']??0),'created_at'=>now(),'updated_at'=>now()]));return back()->with('success','Address saved.');}
 public function addressDelete(Request $r,int $address){DB::table('addresses')->where('id',$address)->where('user_id',$r->user()->id)->delete();return back()->with('success','Address removed.');}
 public function security(){return view('customer.security');}
 public function password(Request $r){$d=$r->validate(['current_password'=>'required','password'=>'required|string|min:8|confirmed']);abort_unless(\Hash::check($d['current_password'],$r->user()->password),422,'Current password is incorrect.');$r->user()->update(['password'=>\Hash::make($d['password'])]);return back()->with('success','Password updated.');}
}
