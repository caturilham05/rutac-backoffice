<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProducts extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_origin_id',
        'product_model_id',
        'product_name',
        'qty',
        'price',
        'sale',
        'discount',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id');
    }
}
