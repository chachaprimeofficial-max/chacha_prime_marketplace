<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProductVariationService
{
    public function sync(int $productId, array $variations): void
    {
        DB::transaction(function () use ($productId, $variations) {
            DB::table('product_variations')->where('product_id', $productId)->delete();
            foreach ($variations as $variation) {
                DB::table('product_variations')->insert([
                    'product_id' => $productId,
                    'name' => $variation['name'] ?? 'Default',
                    'sku' => $variation['sku'] ?? null,
                    'attributes_json' => json_encode($variation['attributes'] ?? []),
                    'retail_price' => $variation['retail_price'] ?? null,
                    'wholesale_price' => $variation['wholesale_price'] ?? null,
                    'group_buying_price' => $variation['group_buying_price'] ?? null,
                    'stock_qty' => $variation['stock_qty'] ?? 0,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }
}
