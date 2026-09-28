<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\AdminSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SettingsController extends Controller
{
 public function index(){return view('admin.settings.index',['settings'=>app(AdminSettingsService::class)->all(),'roles'=>DB::table('admin_roles')->get()]);}
 public function update(Request $r){$data=$r->validate(['settings'=>'nullable|array']);$service=app(AdminSettingsService::class);foreach($data['settings']??[] as $key=>$value){$service->set($key,$value);DB::table('admin_audit_logs')->insert(['user_id'=>$r->user()->id,'action'=>'setting_updated','entity_type'=>'platform_setting','old_values'=>null,'new_values'=>json_encode(['key'=>$key,'value'=>$value]),'ip_address'=>$r->ip(),'user_agent'=>$r->userAgent(),'created_at'=>now()]);}return back()->with('success','Platform settings updated.');}
 public function audit(){return view('admin.settings.audit',['logs'=>DB::table('admin_audit_logs')->leftJoin('users','users.id','=','admin_audit_logs.user_id')->select('admin_audit_logs.*','users.name as user_name')->latest('admin_audit_logs.id')->paginate(50)]);}
}
