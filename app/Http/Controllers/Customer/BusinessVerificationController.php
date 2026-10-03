<?php
namespace App\Http\Controllers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class BusinessVerificationController extends Controller
{
 public function show(){return view('customer.business-verification',['verification'=>DB::table('business_verifications')->where('user_id',auth()->id())->first()]);}
 public function store(Request $request){abort_unless((bool)$request->user()->two_factor_enabled,403,'Enable two-factor authentication before submitting business verification.');$data=$request->validate(['business_name'=>'required|string|max:255','business_email'=>'required|email|max:190','country'=>'required|string|max:100','business_address'=>'required|string|max:2000','identity_type'=>'required|in:passport,national_id,driving_license','identity_document'=>'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);$path=$request->file('identity_document')->store('business-verifications');DB::table('business_verifications')->updateOrInsert(['user_id'=>auth()->id()],array_merge(collect($data)->except('identity_document')->all(),['identity_document_path'=>$path,'status'=>'pending','reviewed_by'=>null,'reviewed_at'=>null,'updated_at'=>now(),'created_at'=>DB::raw('COALESCE(created_at,NOW())')]));return back()->with('success','Business verification submitted for review.');}
}