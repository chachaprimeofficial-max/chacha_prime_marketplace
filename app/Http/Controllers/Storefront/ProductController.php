<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['brand', 'category', 'media'])
            ->where('status', 'active')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim((string) $request->input('q'));
                $q->where(function ($x) use ($term) {
                    $x->where('title', 'like', "%{$term}%")
                      ->orWhere('sku', 'like', "%{$term}%")
                      ->orWhere('product_code', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $request->input('category')));
            })
            ->when($request->filled('min_price'), fn ($q) => $q->where('retail_price', '>=', (float) $request->input('min_price')))
            ->when($request->filled('max_price'), fn ($q) => $q->where('retail_price', '<=', (float) $request->input('max_price')))
            ->when($request->boolean('in_stock'), fn ($q) => $q->where('stock_qty', '>', 0));

        switch ($request->input('sort')) {
            case 'price_asc': $products->orderBy('retail_price'); break;
            case 'price_desc': $products->orderByDesc('retail_price'); break;
            case 'rating': $products->orderByDesc('rating_avg')->orderByDesc('review_count'); break;
            case 'popular': $products->orderByDesc('total_sold'); break;
            default: $products->latest('id');
        }

        $products = $products->paginate(24)->withQueryString();
        $categories = \DB::table('categories')->where('status', 'active')->orderBy('name')->get();

        return view('storefront.products.index', compact('products', 'categories'));
    }

    public function showBySku(string $sku){ $product=Product::where('sku',$sku)->firstOrFail(); abort_unless($product->status === 'active',404); $product->load(['brand','category','media','variants']); return view('storefront.products.show',compact('product')); }

    public function show(Product $product)
    {
        abort_unless($product->status === 'active', 404);
        $product->load(['brand', 'category', 'media', 'variants']);
        return view('storefront.products.show', compact('product'));
    }
}
