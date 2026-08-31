<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiscountItem extends Model
{
    protected $fillable = [
        'product_origin_id',
        'item_name',
        'product_model_id',
        'model_name',
        'model_original_price',
        'model_promotion_price',
        'model_normal_stock',
        'model_promotion_stock',
        'purchase_limit',
        'item_original_price',
        'item_promotion_price',
        'normal_stock',
        'item_promotion_stock',
    ];
}
