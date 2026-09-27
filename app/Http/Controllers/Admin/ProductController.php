<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = DB::table('products')->latest('id')->paginate(25);
        return view('admin.products.index', compact('products'));
    }

    public function store(Request $request, ProductQrService $qr)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'], 'title' => ['required','string','max:255'],
            'sku' => ['required','string','max:100','unique:products,sku'],
            'short_description' => ['nullable','string'], 'description' => ['nullable','string'],
            'retail_price' => ['required','numeric','min:0'], 'wholesale_price' => ['nullable','numeric','min:0'],
            'group_buying_price' => ['nullable','numeric','min:0'], 'stock_qty' => ['required','integer','min:0'],
            'brand_name' => ['nullable','string','max:255'], 'category_id' => ['nullable','integer'],
        ]);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::lower(Str::random(6));
        $data['status'] = 'active';
        $data['created_at'] = now(); $data['updated_at'] = now();
        $productId = DB::table('products')->insertGetId($data);
        $qrData = $qr->make($data['sku']);
        DB::table('products')->where('id', $productId)->update(['qr_payload' => $qrData['payload'], 'updated_at' => now()]);
        return redirect()->route('admin.products.index')->with('success', 'Product created with QR identity.');
    }
}
