<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    protected $table = 'product_media';
    public $timestamps = false;
    protected $guarded = [];

    public function product() { return $this->belongsTo(Product::class); }
}
