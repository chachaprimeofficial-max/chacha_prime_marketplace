<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $guarded = [];

    protected $casts = [
        'highlights' => 'array',
        'features' => 'array',
        'additional_details' => 'array',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'group_price' => 'decimal:2',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function media() { return $this->hasMany(ProductMedia::class); }
    public function variants() { return $this->hasMany(ProductVariant::class); }
}
