<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductMediaService
{
    public function attach(int $productId, array $images = [], array $videos = []): void
    {
        $position = 0;
        foreach ($images as $file) {
            if (!$file instanceof UploadedFile) continue;
            DB::table('product_media')->insert([
                'product_id' => $productId,
                'media_type' => 'image',
                'path' => $file->store('products/images', 'public'),
                'sort_order' => $position++,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach ($videos as $file) {
            if (!$file instanceof UploadedFile) continue;
            DB::table('product_media')->insert([
                'product_id' => $productId,
                'media_type' => 'video',
                'path' => $file->store('products/videos', 'public'),
                'sort_order' => $position++,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
