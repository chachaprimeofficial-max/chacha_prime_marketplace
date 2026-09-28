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
    public function index(Request $request): View
    {
        $query=DB::table('products')->leftJoin('categories','categories.id','=','products.category_id')->select('products.*','categories.name as category_name');
        if($request->filled('q'))$query->where(fn($q)=>$q->where('products.name','like','%'.$request->q.'%')->orWhere('products.sku','like','%'.$request->q.'%'));
        if($request->filled('status'))$query->where('products.status',$request->status);
        $products=$query->latest('products.id')->paginate(25)->withQueryString(); return view('admin.products.index',compact('products'));
    }
    public function create(): View { $categories=DB::table('categories')->where('status','active')->orderBy('name')->get(); return view('admin.products.form',compact('categories')); }
    public function store(Request $request,ProductQrService $qr){
        $data=$this->validated($request); $data['slug']=Str::slug($data['title']).'-'.Str::lower(Str::random(6)); $data['return_window_business_days']=config('chacha.brand.return_window_business_days',7); $data['created_at']=now(); $data['updated_at']=now();
        $id=DB::table('products')->insertGetId($data); DB::table('products')->where('id',$id)->update(['qr_payload'=>$qr->make($data['sku'])['payload'],'updated_at'=>now()]); return redirect()->route('admin.products.edit',$id)->with('success','Product created successfully.');
    }
    public function edit(int $product): View { $item=DB::table('products')->where('id',$product)->first(); abort_unless($item,404); $categories=DB::table('categories')->where('status','active')->orderBy('name')->get(); $variations=DB::table('product_variations')->where('product_id',$product)->orderBy('id')->get(); $media=DB::table('product_media')->where('product_id',$product)->orderBy('sort_order')->get(); return view('admin.products.form',compact('item','categories','variations','media')); }
    public function update(Request $request,int $product,ProductQrService $qr){ $item=DB::table('products')->where('id',$product)->first(); abort_unless($item,404); $data=$this->validated($request,$product); $data['slug']=$item->slug; $data['return_window_business_days']=config('chacha.brand.return_window_business_days',7); $data['updated_at']=now(); if($data['sku']!==$item->sku)$data['qr_payload']=$qr->make($data['sku'])['payload']; DB::table('products')->where('id',$product)->update($data); return back()->with('success','Product updated successfully.'); }
    public function variationStore(Request $request,int $product){ $request->validate(['name'=>'required|string|max:190','sku'=>'nullable|string|max:120|unique:product_variations,sku','attributes_json'=>'nullable|json','retail_price'=>'nullable|numeric|min:0','wholesale_price'=>'nullable|numeric|min:0','group_buying_price'=>'nullable|numeric|min:0','stock_qty'=>'required|integer|min:0']); DB::table('product_variations')->insert(array_merge($request->only(['name','sku','attributes_json','retail_price','wholesale_price','group_buying_price','stock_qty']),['product_id'=>$product,'created_at'=>now(),'updated_at'=>now()])); $this->syncStock($product); return back()->with('success','Variant added.'); }
    public function variationDelete(int $product,int $variation){ DB::table('product_variations')->where('id',$variation)->where('product_id',$product)->delete(); $this->syncStock($product); return back()->with('success','Variant removed.'); }
    public function inventory(Request $request,int $product){ $request->validate(['adjustment'=>'required|integer','reason'=>'required|string|max:255']); $item=DB::table('products')->where('id',$product)->lockForUpdate()->first(); abort_unless($item,404); $qty=max(0,(int)$item->stock_qty+(int)$request->adjustment); DB::table('products')->where('id',$product)->update(['stock_qty'=>$qty,'status'=>$qty===0?'out_of_stock':($item->status==='out_of_stock'?'active':$item->status),'updated_at'=>now()]); return back()->with('success','Inventory adjusted.'); }
    public function mediaStore(Request $request,int $product){ $request->validate(['path'=>'required|string|max:1000','media_type'=>'required|in:image,video','sort_order'=>'nullable|integer|min:0']); DB::table('product_media')->insert(['product_id'=>$product,'path'=>$request->path,'media_type'=>$request->media_type,'sort_order'=>$request->sort_order??0,'created_at'=>now(),'updated_at'=>now()]); return back()->with('success','Media added.'); }
    public function mediaDelete(int $product,int $media){ DB::table('product_media')->where('id',$media)->where('product_id',$product)->delete(); return back()->with('success','Media removed.'); }
    private function syncStock(int $product): void { $sum=(int)DB::table('product_variations')->where('product_id',$product)->sum('stock_qty'); DB::table('products')->where('id',$product)->update(['stock_qty'=>$sum,'status'=>$sum===0?'out_of_stock':'active','updated_at'=>now()]); }
    private function validated(Request $request,?int $product=null): array { return $request->validate(['name'=>'required|string|max:255','title'=>'required|string|max:255','sku'=>'required|string|max:100|unique:products,sku'.($product?','.$product:''),'short_description'=>'nullable|string','description'=>'nullable|string','retail_price'=>'required|numeric|min:0','wholesale_price'=>'nullable|numeric|min:0','group_buying_price'=>'nullable|numeric|min:0','stock_qty'=>'required|integer|min:0','brand_name'=>'nullable|string|max:255','category_id'=>'nullable|integer','status'=>'required|in:draft,active,inactive,out_of_stock','seo_title'=>'nullable|string|max:255','seo_description'=>'nullable|string']); }
}
