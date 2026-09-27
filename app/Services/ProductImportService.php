<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductImportService
{
    public function import(array $source): int
    {
        $title = trim($source['title'] ?? $source['name'] ?? 'Imported Product');
        $sku = trim($source['sku'] ?? 'CP-' . strtoupper(Str::random(10)));

        return DB::transaction(function () use ($source, $title, $sku) {
            $existing = DB::table('products')->where('sku', $sku)->first();
            $data = [
                'name' => $source['name'] ?? $title,
                'title' => $title,
                'sku' => $sku,
                'short_description' => $source['short_description'] ?? null,
                'description' => $source['description'] ?? null,
                'brand_name' => $source['brand_name'] ?? null,
                'retail_price' => (float) ($source['retail_price'] ?? 0),
                'wholesale_price' => isset($source['wholesale_price']) ? (float) $source['wholesale_price'] : null,
                'group_buying_price' => isset($source['group_buying_price']) ? (float) $source['group_buying_price'] : null,
                'stock_qty' => (int) ($source['stock_qty'] ?? 0),
                'status' => 'draft',
                'updated_at' => now(),
            ];

            if ($existing) {
                DB::table('products')->where('id', $existing->id)->update($data);
                $productId = $existing->id;
            } else {
                $data['slug'] = Str::slug($title) . '-' . Str::lower(Str::random(6));
                $data['created_at'] = now();
                $productId = DB::table('products')->insertGetId($data);
            }

            foreach (($source['images'] ?? []) as $index => $url) {
                if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
                DB::table('product_import_media')->insert([
                    'product_id' => $productId, 'media_type' => 'image', 'source_url' => $url,
                    'sort_order' => $index, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            foreach (($source['videos'] ?? []) as $index => $url) {
                if (!filter_var($url, FILTER_VALIDATE_URL)) continue;
                DB::table('product_import_media')->insert([
                    'product_id' => $productId, 'media_type' => 'video', 'source_url' => $url,
                    'sort_order' => $index, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::table('product_imports')->insert([
                'product_id' => $productId,
                'source_name' => $source['source'] ?? 'manual',
                'source_url' => $source['source_url'] ?? null,
                'raw_data_json' => json_encode($source, JSON_UNESCAPED_UNICODE),
                'status' => 'imported',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            return $productId;
        });
    }
}
