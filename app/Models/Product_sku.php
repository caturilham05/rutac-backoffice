<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_sku extends Model
{
    protected $table    = 'product_skus';
    protected $fillable = ['product_id', 'product_variant_id', 'name', 'stock', 'original_price', 'discount_price', 'product_model_id'];
}
