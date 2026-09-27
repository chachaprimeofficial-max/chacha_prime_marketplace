<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class CartService
{
    public function add(int $productId, ?int $variantId, int $quantity = 1): array
    {
        if ($quantity < 1) throw new RuntimeException('Quantity must be at least 1.');
        $product = DB::table('products')->where('id', $productId)->where('status', 'active')->first();
        if (!$product) throw new RuntimeException('Product is unavailable.');
        $variant = $variantId ? DB::table('product_variants')->where('id', $variantId)->where('product_id', $productId)->first() : null;
        $available = (int) (($variant ?: $product)->stock_qty) - (int) (($variant ?: $product)->reserved_qty);
        if ($quantity > $available) throw new RuntimeException('Requested quantity is not available.');
        return ['product_id' => $productId, 'variant_id' => $variantId, 'quantity' => $quantity, 'price' => $variant?->price ?? $product->retail_price];
    }
}
