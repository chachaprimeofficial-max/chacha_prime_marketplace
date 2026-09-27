<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';
    protected $guarded = [];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'group_price' => 'decimal:2',
    ];

    public function product() { return $this->belongsTo(Product::class); }
}
