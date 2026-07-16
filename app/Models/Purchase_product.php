<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Purchase_product extends Model
{
    protected $table    = 'purchase_products';
    protected $fillable = ['purchase_id', 'product_id', 'product_name', 'cat_id', 'cat_name', 'price', 'qty'];

    /**
     * Get the user associated with the Purchase_product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchase(): HasOne
    {
        return $this->hasOne(Purchase::class, 'purchase_id', 'id');
    }
}
