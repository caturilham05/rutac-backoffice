<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product_category extends Model
{
    protected $table    = 'product_categories';
    protected $fillable = ['name'];

    public static function productCategoryUpsert($data = [])
    {
        return self::updateOrCreate(
            [
                'id' => $data['id'] ?? null
            ],
            [
                'name' => $data['name'],
            ]
        );
    }
}
