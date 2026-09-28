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
        $query = DB::table('products')->leftJoin('categories','categories.id','=','products.category_id')->select('products.*','categories.name as category_name');
        if ($request->filled('q')) $query->where(fn($q) => $q->where('products.name','like','%'.$request->q.'%')->orWhere('products.sku','like','%'.$request->q.'%'));
        if ($request->filled('status')) $query->where('products.status',$request->status);
        $products = $query->latest('products.id')->paginate(25)->withQueryString();
        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories=DB::table('categories')->where('status','active')->orderBy('name')->get();
        return view('admin.products.form',compact('categories'));
    }

    public function store(Request $request, ProductQrService $qr)
    {
        $data=$request->validate(['name'=>'required|string|max:255','title'=>'required|string|max:255','sku'=>'required|string|max:100|unique:products,sku','short_description'=>'nullable|string','description'=>'nullable|string','retail_price'=>'required|numeric|min:0','wholesale_price'=>'nullable|numeric|min:0','group_buying_price'=>'nullable|numeric|min:0','stock_qty'=>'required|integer|min:0','brand_name'=>'nullable|string|max:255','category_id'=>'nullable|integer','status'=>'required|in:draft,active,inactive,out_of_stock','seo_title'=>'nullable|string|max:255','seo_description'=>'nullable|string']);
        $data['slug']=Str::slug($data['title']).'-'.Str::lower(Str::random(6)); $data['return_window_business_days']=config('chacha.brand.return_window_business_days',7); $data['created_at']=now(); $data['updated_at']=now();
        $id=DB::table('products')->insertGetId($data); $qrData=$qr->make($data['sku']); DB::table('products')->where('id',$id)->update(['qr_payload'=>$qrData['payload'],'updated_at'=>now()]);
        return redirect()->route('admin.products.edit',$id)->with('success','Product created successfully.');
    }

    public function edit(int $product): View
    {
        $item=DB::table('products')->where('id',$product)->first(); abort_unless($item,404);
        $categories=DB::table('categories')->where('status','active')->orderBy('name')->get();
        $variations=DB::table('product_variations')->where('product_id',$product)->orderBy('id')->get();
        return view('admin.products.form',compact('item','categories','variations'));
    }

    public function update(Request $request,int $product, ProductQrService $qr)
    {
        $item=DB::table('products')->where('id',$product)->first(); abort_unless($item,404);
        $data=$request->validate(['name'=>'required|string|max:255','title'=>'required|string|max:255','sku'=>'required|string|max:100|unique:products,sku,'.$product,'short_description'=>'nullable|string','description'=>'nullable|string','retail_price'=>'required|numeric|min:0','wholesale_price'=>'nullable|numeric|min:0','group_buying_price'=>'nullable|numeric|min:0','stock_qty'=>'required|integer|min:0','brand_name'=>'nullable|string|max:255','category_id'=>'nullable|integer','status'=>'required|in:draft,active,inactive,out_of_stock','seo_title'=>'nullable|string|max:255','seo_description'=>'nullable|string']);
        $data['slug']=$item->slug; $data['return_window_business_days']=config('chacha.brand.return_window_business_days',7); $data['updated_at']=now();
        if ($data['sku'] !== $item->sku) $data['qr_payload']=$qr->make($data['sku'])['payload'];
        DB::table('products')->where('id',$product)->update($data); return back()->with('success','Product updated successfully.');
    }
}
