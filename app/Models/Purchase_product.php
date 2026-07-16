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
        return $this->hasOne(Purchase::class, 'id', 'purchase_id');
    }

    public static function purchaseProductPagination(int $per_page = 15, array $filter = [], ?string $sort = null, $direction = 'asc', bool $pagination = true)
    {
        $query = self::with(['purchase']);

        if (!empty($filter['invoice'])) {
            $query->whereHas('purchase', fn($q) => $q->where('invoice', 'like', '%' . $filter['invoice'] . '%'));
        }

        if (!empty($filter['product_name'])) {
            $query->where('product_name', 'like', '%'.$filter['product_name'].'%');
        }

        if (!empty($filter['cat_name'])) {
            $query->where('cat_name', 'like', '%'.$filter['cat_name'].'%');
        }

        if ($sort && in_array($sort, ['invoice', 'product_name', 'cat_name'])) {
            if ($sort === 'invoice') {
                $query->orderBy(
                    Purchase::select('invoice')->whereColumn('id', 'purchase_id')->limit(1),
                    $direction
                );
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $purchase_products = $query->paginate($per_page);
        return $purchase_products;
    }
}
