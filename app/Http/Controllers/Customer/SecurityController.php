<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SecurityController extends Controller
{
 public function index(){return view('customer.security.index',['user'=>auth()->user(),'events'=>DB::table('login_security_events')->where('user_id',auth()->id())->latest()->limit(20)->get()]);}
 public function enable2fa(Request $r){$d=$r->validate(['secret'=>'required|string|min:16']);app(TwoFactorService::class)->enable(auth()->id(),$d['secret']);return back()->with('success','Two-factor authentication enabled.');}
 public function disable2fa(){app(TwoFactorService::class)->disable(auth()->id());return back()->with('success','Two-factor authentication disabled.');}
 public function recoveryCodes(){return response()->json(['codes'=>app(TwoFactorService::class)->recoveryCodes(auth()->id())]);}
 public function password(Request $r){$d=$r->validate(['current_password'=>'required','password'=>'required|string|min:8|confirmed']);abort_unless(\Hash::check($d['current_password'],$r->user()->password),422,'Current password is incorrect.');$r->user()->forceFill(['password'=>\Hash::make($d['password'])])->save();DB::table('login_security_events')->insert(['user_id'=>$r->user()->id,'event'=>'password_changed','ip_address'=>$r->ip(),'user_agent'=>$r->userAgent(),'created_at'=>now()]);return back()->with('success','Password changed successfully.');}
}
