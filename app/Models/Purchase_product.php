<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase_product extends Model
{
    protected $table    = 'purchase_products';
    protected $fillable = ['purchase_id', 'product_id', 'product_name', 'cat_id', 'cat_name', 'price', 'qty'];
}
