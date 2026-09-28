<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class BusinessVerificationController extends Controller
{
 public function index(){return view('admin.business-verifications.index',['verifications'=>DB::table('business_verifications as b')->join('users as u','u.id','=','b.user_id')->orderByRaw("FIELD(b.status,'pending','verified','rejected')")->orderByDesc('b.created_at')->get(['b.*','u.name','u.email'])]);}
 public function update(Request $request,int $verification){$data=$request->validate(['status'=>'required|in:verified,rejected']);DB::transaction(function()use($data,$verification,$request){$v=DB::table('business_verifications')->where('id',$verification)->lockForUpdate()->firstOrFail();DB::table('business_verifications')->where('id',$verification)->update(['status'=>$data['status'],'reviewed_by'=>$request->user()->id,'reviewed_at'=>now(),'updated_at'=>now()]);});return back()->with('success','Business verification updated.');}
}