<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin(){return view('auth.login');}
    public function login(Request $request,TotpService $totp)
    {
        $data=$request->validate(['email'=>'required|email','password'=>'required|string','code'=>'nullable|digits:6']);
        $user=User::where('email',$data['email'])->first();
        if(!$user || !Hash::check($data['password'],$user->password) || $user->status!=='active'){
            $this->event($user,'login_failed'); return back()->withErrors(['email'=>'The email or password is incorrect.'])->withInput($request->only('email'));
        }
        if($user->two_factor_enabled){
            if(empty($data['code'])){
                $request->session()->put('auth.2fa_user_id',$user->id);
                return redirect()->route('login')->with('two_factor_required',true)->withInput($request->only('email'));
            }
            $secret=decrypt($user->two_factor_secret);
            if(!$totp->verify($secret,$data['code'])){
                $this->event($user,'login_2fa_failed'); return back()->withErrors(['code'=>'The authenticator code is invalid.'])->withInput($request->only('email'));
            }
            $request->session()->forget('auth.2fa_user_id');
        }
        Auth::login($user,$request->boolean('remember'));
        $request->session()->regenerate();
        $this->event($user,'login_success');
        return redirect()->intended(route('customer.dashboard'));
    }
    public function showRegister(){return view('auth.register');}
    public function register(Request $request)
    {
        $data=$request->validate([
            'name'=>'required|string|max:150','email'=>'required|email|max:190|unique:users,email',
            'phone'=>'nullable|string|max:40','password'=>['required','confirmed',Password::min(8)->mixedCase()->numbers()]
        ]);
        $user=DB::transaction(function() use ($data){
            $u=User::create(['name'=>$data['name'],'email'=>$data['email'],'phone'=>$data['phone']??null,'password'=>$data['password'],'status'=>'active']);
            DB::table('customer_profiles')->insert(['user_id'=>$u->id,'created_at'=>now(),'updated_at'=>now()]);
            DB::table('wallets')->insert(['user_id'=>$u->id,'currency'=>config('chacha.brand.default_currency','USD'),'balance'=>0,'status'=>'active','created_at'=>now(),'updated_at'=>now()]);
            return $u;
        });
        Auth::login($user);
        $request->session()->regenerate();
        $this->event($user,'register');
        return redirect()->route('customer.dashboard');
    }
    public function logout(Request $request)
    {
        $user=$request->user(); if($user) $this->event($user,'logout');
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return redirect()->route('login');
    }
    public function showForgot(){return view('auth.forgot-password');}
    public function sendReset(Request $request)
    {
        $data=$request->validate(['email'=>'required|email']);
        $user=User::where('email',$data['email'])->first();
        if($user){
            $token=Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email'=>$user->email],
                ['token'=>Hash::make($token),'created_at'=>now()]
            );
            $url=url('/reset-password').'?token='.urlencode($token).'&email='.urlencode($user->email);
            Mail::to($user->email)->send(new PasswordResetMail($url));
        }
        return back()->with('status','If an account exists for that email, reset instructions have been sent.');
    }
    public function showReset(Request $request)
    {
        $data=$request->validate(['token'=>'required|string','email'=>'required|email']);
        $row=DB::table('password_reset_tokens')->where('email',$data['email'])->first();
        abort_unless($row && now()->diffInMinutes($row->created_at)<=60 && Hash::check($data['token'],$row->token), 403);
        return view('auth.reset-password',['token'=>$data['token'],'email'=>$data['email']]);
    }
    public function reset(Request $request)
    {
        $data=$request->validate([
            'token'=>'required|string','email'=>'required|email',
            'password'=>['required','confirmed',Password::min(8)->mixedCase()->numbers()]
        ]);
        return DB::transaction(function() use($data,$request){
            $row=DB::table('password_reset_tokens')->where('email',$data['email'])->lockForUpdate()->first();
            if(!$row || now()->diffInMinutes($row->created_at)>60 || !Hash::check($data['token'],$row->token))
                return back()->withErrors(['email'=>'This password reset link is invalid or expired.']);
            $user=User::where('email',$data['email'])->lockForUpdate()->firstOrFail();
            $user->password=$data['password']; $user->save();
            DB::table('password_reset_tokens')->where('email',$data['email'])->delete();
            Auth::logout();
            $request->session()->invalidate(); $request->session()->regenerateToken();
            return redirect()->route('login')->with('status','Your password has been reset. Please sign in again.');
        });
    }
    private function event(?User $user,string $event):void
    {
        DB::table('login_security_events')->insert(['user_id'=>$user?->id,'event'=>$event,'ip_address'=>request()->ip(),'user_agent'=>substr((string)request()->userAgent(),0,1000),'created_at'=>now()]);
    }
}