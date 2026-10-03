<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class SecurityController extends Controller
{
 public function index(){return view('customer.security',['twoFactorEnabled'=>(bool)auth()->user()->two_factor_enabled]);}
 public function password(Request $r){$d=$r->validate(['current_password'=>'required','password'=>'required|string|min:8|confirmed']);abort_unless(Hash::check($d['current_password'],$r->user()->password),422,'Current password is incorrect.');$r->user()->update(['password'=>Hash::make($d['password'])]);return back()->with('success','Password changed successfully.');}
 public function setup2fa(Request $r,TotpService $totp){$secret=$totp->secret();$r->session()->put('pending_2fa_secret',$secret);return view('customer.two-factor-setup',['secret'=>$secret,'uri'=>$totp->provisioningUri($secret,$r->user()->email)]);}
 public function confirm2fa(Request $r,TotpService $totp){$d=$r->validate(['code'=>'required|string|size:6']);$secret=$r->session()->get('pending_2fa_secret');abort_unless($secret&&$totp->verify($secret,$d['code']),422,'Invalid authenticator code.');$r->user()->update(['two_factor_enabled'=>1,'two_factor_secret'=>encrypt($secret),'two_factor_confirmed_at'=>now()]);DB::table('two_factor_recovery_codes')->where('user_id',$r->user()->id)->delete();$codes=[];for($i=0;$i<8;$i++){ $raw=strtoupper(bin2hex(random_bytes(5)));DB::table('two_factor_recovery_codes')->insert(['user_id'=>$r->user()->id,'code_hash'=>Hash::make($raw),'created_at'=>now(),'updated_at'=>now()]);$codes[]=$raw; }$r->session()->forget('pending_2fa_secret');return view('customer.two-factor-recovery',['codes'=>$codes]);}
 public function enable2fa(Request $r){return $this->confirm2fa($r,app(TotpService::class));}
 public function disable2fa(Request $r,TotpService $totp){$d=$r->validate(['code'=>'required|string|size:6','current_password'=>'required']);abort_unless(Hash::check($d['current_password'],$r->user()->password),422,'Current password is incorrect.');$secret=$r->user()->two_factor_secret?decrypt($r->user()->two_factor_secret):null;abort_unless($secret&&$totp->verify($secret,$d['code']),422,'Invalid authenticator code.');$r->user()->update(['two_factor_enabled'=>0,'two_factor_secret'=>null]);DB::table('two_factor_recovery_codes')->where('user_id',$r->user()->id)->delete();return back()->with('success','Two-factor authentication disabled.');}
 public function recoveryCodes(){abort_unless((bool)auth()->user()->two_factor_enabled,403,'Two-factor authentication is not enabled.');return response()->json(['message'=>'Recovery codes are shown only once when 2FA is confirmed. Generate a new set by disabling and re-enabling 2FA.']);}
}