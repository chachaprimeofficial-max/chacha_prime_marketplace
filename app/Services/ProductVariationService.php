<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class ProductVariationService
{
    public function sync(int $productId,array $variations): void
    {
        DB::transaction(function() use ($productId,$variations) {
            DB::table('product_variants')->where('product_id',$productId)->delete();
            foreach ($variations as $variation) {
                $sku=$variation['sku'] ?? null;
                if (!$sku) continue;
                DB::table('product_variants')->insert([
                    'product_id'=>$productId,
                    'sku'=>$sku,
                    'attributes'=>json_encode($variation['attributes'] ?? []),
                    'price'=>$variation['price'] ?? ($variation['retail_price'] ?? null),
                    'wholesale_price'=>$variation['wholesale_price'] ?? null,
                    'group_price'=>$variation['group_price'] ?? ($variation['group_buying_price'] ?? null),
                    'stock_qty'=>(int)($variation['stock_qty'] ?? 0),
                    'reserved_qty'=>(int)($variation['reserved_qty'] ?? 0),
                    'qr_value'=>url('/products/sku/'.$sku),
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }
            $sum=(int)DB::table('product_variants')->where('product_id',$productId)->sum('stock_qty');
            DB::table('products')->where('id',$productId)->update(['stock_qty'=>$sum,'updated_at'=>now()]);
        });
    }
}