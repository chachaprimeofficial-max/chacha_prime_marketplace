<?php

namespace App\Services;

use Illuminate\Support\Str;

class SkuService
{
    public function generate(string $categoryCode = 'GEN', ?int $id = null): string
    {
        $number = $id ?? random_int(1, 99999999);
        return 'CP-' . strtoupper(Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $categoryCode), 8, '')) . '-' . str_pad((string) $number, 8, '0', STR_PAD_LEFT);
    }
}
