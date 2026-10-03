<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class BusinessVerificationController extends Controller
{
 public function index(){return view('admin.business-verifications.index',['verifications'=>DB::table('business_verifications as b')->join('users as u','u.id','=','b.user_id')->orderByRaw("FIELD(b.status,'pending','verified','rejected')")->orderByDesc('b.created_at')->get(['b.*','u.name','u.email'])]);}
 public function document(int $verification){
  $v=DB::table('business_verifications')->where('id',$verification)->firstOrFail();
  abort_unless(Storage::exists($v->identity_document_path),404);
  return Storage::download($v->identity_document_path,'business-verification-'.$v->id.'.'.pathinfo($v->identity_document_path,PATHINFO_EXTENSION));
 }
 public function update(Request $request,int $verification){$data=$request->validate(['status'=>'required|in:verified,rejected']);DB::transaction(function()use($data,$verification,$request){$v=DB::table('business_verifications')->where('id',$verification)->lockForUpdate()->firstOrFail();DB::table('business_verifications')->where('id',$verification)->update(['status'=>$data['status'],'reviewed_by'=>$request->user()->id,'reviewed_at'=>now(),'updated_at'=>now()]);
  DB::table('admin_audit_logs')->insert(['user_id'=>$request->user()->id,'action'=>'business_verification_'.$data['status'],'entity_type'=>'business_verification','entity_id'=>$verification,'old_values'=>json_encode(['status'=>$v->status]),'new_values'=>json_encode(['status'=>$data['status']]),'ip_address'=>$request->ip(),'user_agent'=>$request->userAgent(),'created_at'=>now()]);});return back()->with('success','Business verification updated.');}
}