<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
 public function index(Request $request): View {
  $query=DB::table('products')->leftJoin('categories','categories.id','=','products.category_id')->select('products.*','categories.name as category_name');
  if($request->filled('q')) $query->where(fn($q)=>$q->where('products.title','like','%'.$request->q.'%')->orWhere('products.sku','like','%'.$request->q.'%')->orWhere('products.product_code','like','%'.$request->q.'%'));
  if($request->filled('status')) $query->where('products.status',$request->status);
  return view('admin.products.index',['products'=>$query->latest('products.id')->paginate(25)->withQueryString()]);
 }
 public function create(): View { return view('admin.products.form',['categories'=>DB::table('categories')->where('status','active')->orderBy('name')->get(),'brands'=>DB::table('brands')->where('status','active')->orderBy('name')->get()]); }
 public function store(Request $request, ProductQrService $qr) {
  $data=$this->validated($request);
  $data['product_code']='CP-'.strtoupper(Str::random(10));
  $data['slug']=Str::slug($data['title']).'-'.Str::lower(Str::random(6));
  $data['qr_value']=$qr->make($data['sku'])['payload'];
  $data['return_window_business_days']=7; $data['created_at']=now(); $data['updated_at']=now();
  $id=DB::table('products')->insertGetId($data);
  return redirect()->route('admin.products.edit',$id)->with('success','Product created successfully.');
 }
 public function edit(int $product): View {
  $item=DB::table('products')->where('id',$product)->first(); abort_unless($item,404);
  $categories=DB::table('categories')->where('status','active')->orderBy('name')->get(); $brands=DB::table('brands')->where('status','active')->orderBy('name')->get();
  $variations=DB::table('product_variants')->where('product_id',$product)->orderBy('id')->get();
  $media=DB::table('product_media')->where('product_id',$product)->orderBy('sort_order')->get();
  return view('admin.products.form',compact('item','categories','brands','variations','media'));
 }
 public function update(Request $request,int $product,ProductQrService $qr) {
  $item=DB::table('products')->where('id',$product)->first(); abort_unless($item,404);
  $data=$this->validated($request,$product); $data['slug']=$item->slug; $data['product_code']=$item->product_code; $data['return_window_business_days']=7; $data['updated_at']=now();
  if($data['sku']!==$item->sku) $data['qr_value']=$qr->make($data['sku'])['payload'];
  DB::table('products')->where('id',$product)->update($data);
  return back()->with('success','Product updated successfully.');
 }
 public function variationStore(Request $request,int $product) {
  abort_unless(DB::table('products')->where('id',$product)->exists(),404);
  $d=$request->validate(['sku'=>'required|string|max:120|unique:product_variants,sku','attributes'=>'required|json','price'=>'nullable|numeric|min:0','wholesale_price'=>'nullable|numeric|min:0','group_price'=>'nullable|numeric|min:0','stock_qty'=>'required|integer|min:0']);
  $d['product_id']=$product; $d['qr_value']=url('/products/sku/'.rawurlencode($d['sku'])); $d['created_at']=now(); $d['updated_at']=now();
  DB::table('product_variants')->insert($d); $this->syncStock($product); return back()->with('success','Variant added.');
 }
 public function variationDelete(int $product,int $variation) { DB::table('product_variants')->where('id',$variation)->where('product_id',$product)->delete(); $this->syncStock($product); return back()->with('success','Variant removed.'); }
 public function inventory(Request $request,int $product) {
  $d=$request->validate(['adjustment'=>'required|integer','reason'=>'required|string|max:255']);
  DB::transaction(function()use($request,$product,$d){$p=DB::table('products')->where('id',$product)->lockForUpdate()->firstOrFail();$before=(int)$p->stock_qty;$after=$before+(int)$d['adjustment'];abort_if($after<0,422,'Stock cannot be negative.');DB::table('products')->where('id',$product)->update(['stock_qty'=>$after,'status'=>$after===0?'out_of_stock':($p->status==='out_of_stock'?'active':$p->status),'updated_at'=>now()]);DB::table('inventory_movements')->insert(['product_id'=>$product,'type'=>'adjustment','quantity'=>(int)$d['adjustment'],'quantity_before'=>$before,'quantity_after'=>$after,'note'=>$d['reason'],'created_by'=>$request->user()->id,'created_at'=>now(),'updated_at'=>now()]);});
  return back()->with('success','Inventory adjusted.');
 }
 public function mediaStore(Request $request,int $product) { $d=$request->validate(['path'=>'required|string|max:500','type'=>'required|in:image,video','alt_text'=>'nullable|string|max:255','sort_order'=>'nullable|integer|min:0','is_primary'=>'nullable|boolean']); DB::table('product_media')->insert(array_merge($d,['product_id'=>$product,'created_at'=>now()])); return back()->with('success','Media added.'); }
 public function mediaDelete(int $product,int $media) { DB::table('product_media')->where('id',$media)->where('product_id',$product)->delete(); return back()->with('success','Media removed.'); }
 private function syncStock(int $product): void { $sum=(int)DB::table('product_variants')->where('product_id',$product)->sum('stock_qty'); DB::table('products')->where('id',$product)->update(['stock_qty'=>$sum,'status'=>$sum===0?'out_of_stock':'active','updated_at'=>now()]); }
 private function validated(Request $request,?int $product=null): array {
  return $request->validate(['title'=>'required|string|max:255','sku'=>'required|string|max:100|unique:products,sku'.($product?','.$product:''),'short_description'=>'nullable|string','description'=>'nullable|string','retail_price'=>'required|numeric|min:0','wholesale_price'=>'nullable|numeric|min:0','group_price'=>'nullable|numeric|min:0','cost_price'=>'nullable|numeric|min:0','stock_qty'=>'required|integer|min:0','low_stock_threshold'=>'nullable|integer|min:0','brand_id'=>'nullable|integer|exists:brands,id','category_id'=>'nullable|integer|exists:categories,id','status'=>'required|in:draft,active,inactive,out_of_stock,archived','ship_from'=>'nullable|string|max:150']);
 }
}