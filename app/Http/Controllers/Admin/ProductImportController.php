<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ProductController;
use App\Services\ProductImportService;
use Illuminate\Http\Request;
class ProductImportController extends Controller {
 public function index(){return view('admin.product-import.index');}
 public function store(Request $r,ProductImportService $imports){$d=$r->validate(['source'=>'required|string|max:80','source_url'=>'nullable|url|max:1000','title'=>'required|string|max:255','sku'=>'nullable|string|max:100','product_code'=>'nullable|string|max:80','retail_price'=>'required|numeric|min:0','wholesale_price'=>'nullable|numeric|min:0','group_price'=>'nullable|numeric|min:0','stock_qty'=>'nullable|integer|min:0','short_description'=>'nullable|string|max:2000','description'=>'nullable|string','images'=>'nullable|string']);$d['images']=array_values(array_filter(array_map('trim',preg_split('/\r?\n|,/',$d['images']??''))));$id=$imports->import($d);return back()->with('success','Product imported as draft #'.$id.'. Review and publish it before selling.');}
}