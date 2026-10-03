<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class BrandController extends Controller
{
 public function index(){return view('admin.brands.index',['brands'=>DB::table('brands')->orderBy('name')->paginate(40)]);}
 public function store(Request $r){$d=$r->validate(['name'=>'required|string|max:150','logo_path'=>'nullable|string|max:500','description'=>'nullable|string','status'=>'required|in:active,inactive']);$d['slug']=Str::slug($d['name']).'-'.Str::lower(Str::random(5));$d['created_at']=now();$d['updated_at']=now();DB::table('brands')->insert($d);return back()->with('success','Brand created.');}
 public function update(Request $r,int $brand){$d=$r->validate(['name'=>'required|string|max:150','logo_path'=>'nullable|string|max:500','description'=>'nullable|string','status'=>'required|in:active,inactive']);DB::table('brands')->where('id',$brand)->update([...$d,'updated_at'=>now()]);return back()->with('success','Brand updated.');}
 public function destroy(int $brand){DB::table('brands')->where('id',$brand)->delete();return back()->with('success','Brand removed.');}
}