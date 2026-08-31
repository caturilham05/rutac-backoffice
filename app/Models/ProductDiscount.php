<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    protected $fillable = [
        'marketplace_id',
        'discount_id',
        'discount_name',
        'start_date',
        'end_date',
        'status',
    ];
}
