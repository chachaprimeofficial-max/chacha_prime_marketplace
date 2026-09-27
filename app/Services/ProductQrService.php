<?php

namespace App\Services;

use Illuminate\Support\Str;

class ProductQrService
{
    public function make(string $sku): array
    {
        $payload = config('app.url') . '/products/sku/' . urlencode($sku);
        return ['sku' => $sku, 'payload' => $payload, 'filename' => 'qr-' . Str::slug($sku) . '.svg'];
    }
}
