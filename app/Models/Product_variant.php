<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_variant extends Model
{
    protected $table    = 'product_variants';
    protected $fillable = ['product_id', 'name', 'name'];
}
