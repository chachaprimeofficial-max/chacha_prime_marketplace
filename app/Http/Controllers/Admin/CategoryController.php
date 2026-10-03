<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CategoryController extends Controller
{
 public function index(){return view('admin.categories.index',['categories'=>DB::table('categories')->leftJoin('categories as p','p.id','=','categories.parent_id')->select('categories.*','p.name as parent_name')->orderBy('sort_order')->orderBy('name')->paginate(40)]);}
 public function store(Request $r){$d=$r->validate(['name'=>'required|string|max:150','parent_id'=>'nullable|integer|exists:categories,id','description'=>'nullable|string','image_path'=>'nullable|string|max:500','sort_order'=>'nullable|integer','status'=>'required|in:active,inactive']);$d['slug']=Str::slug($d['name']).'-'.Str::lower(Str::random(5));$d['created_at']=now();$d['updated_at']=now();DB::table('categories')->insert($d);return back()->with('success','Category created.');}
 public function update(Request $r,int $category){$d=$r->validate(['name'=>'required|string|max:150','parent_id'=>'nullable|integer|exists:categories,id','description'=>'nullable|string','image_path'=>'nullable|string|max:500','sort_order'=>'nullable|integer','status'=>'required|in:active,inactive']);DB::table('categories')->where('id',$category)->update([...$d,'updated_at'=>now()]);return back()->with('success','Category updated.');}
 public function destroy(int $category){DB::table('categories')->where('id',$category)->delete();return back()->with('success','Category removed.');}
}