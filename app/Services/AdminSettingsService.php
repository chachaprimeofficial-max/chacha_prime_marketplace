<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
class AdminSettingsService
{
 public function get(string $key,$default=null){$v=DB::table('platform_settings')->where('setting_key',$key)->value('setting_value');return $v===null?$default:$v;}
 public function set(string $key,$value,string $type='string'):void{DB::table('platform_settings')->updateOrInsert(['setting_key'=>$key],['setting_value'=>is_array($value)?json_encode($value):((is_bool($value)?($value?'1':'0'):(string)$value)),'setting_type'=>$type,'updated_by'=>auth()->id(),'updated_at'=>now(),'created_at'=>now()]);}
 public function all(){return DB::table('platform_settings')->orderBy('setting_key')->get();}
}
