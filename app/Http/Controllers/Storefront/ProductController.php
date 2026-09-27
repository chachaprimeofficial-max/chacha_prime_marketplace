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
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->string('q') . '%'))
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('storefront.products.index', compact('products'));
    }

    public function show(Product $product)
    {
        abort_unless($product->status === 'active', 404);
        $product->load(['brand', 'category', 'media', 'variants']);
        return view('storefront.products.show', compact('product'));
    }
}
