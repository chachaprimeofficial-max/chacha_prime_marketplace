<?php

namespace App\Services;

use Illuminate\Support\Str;

class ProductQrService
{
    public function make(string $sku): array
    {
        $payload = url('/products/sku/' . rawurlencode($sku));
        return [
            'sku' => $sku,
            'payload' => $payload,
            'filename' => 'qr-' . Str::slug($sku) . '.svg',
        ];
    }
}
