<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    protected $table    = 'purchases';
    protected $fillable = ['invoice', 'vendor', 'price'];
    protected $appends  = ['created_at_formatted'];

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->translatedFormat('d F Y');
    }

    public static function purchaseInsert(array $data)
    {
        return DB::transaction(function () use ($data) {
            $prices      = array_column($data['products'], 'price');
            $price_total = array_sum($prices);

            $purchase = self::create([
                'vendor'  => $data['vendor'],
                'invoice' => $data['invoice'],
                'price'   => $price_total
            ]);

            $product_ids    = array_column($data['products'], 'product_id');
            $products       = Product::whereIn('id', $product_ids)->get()->keyBy('id');
            $product_insert = [];
            foreach ($data['products'] as $product) {
                $product_get    = $products[$product['product_id']];
                $product_insert[] = [
                    'purchase_id'  => $purchase->id,
                    'product_id'   => $product['product_id'],
                    'product_name' => $product_get->name ?? '',
                    'cat_id'       => $product_get->cat_id ?? '',
                    'cat_name'     => $product_get->cat_name ?? '',
                    'price'        => $product['price'],
                    'qty'          => $product['qty'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }


            Purchase_product::insert($product_insert);

            $purchases = self::with('purchase_products')->where('id', $purchase->id)->first();

            return $purchases;
        });
    }


    /**
     * Get all of the purchase_products for the Purchase
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_products(): HasMany
    {
        return $this->hasMany(Purchase_product::class, 'purchase_id', 'id');
    }

    public static function purchasePagination(int $per_page = 15, array $filters = [], ?string $sort = null, string $direction = 'asc', bool $pagination = true)
    {
        $query = self::with(['purchase_products']);

        if (!empty($filters['invoice'])) {
            $query->where('invoice', 'like', '%'.$filters['invoice'].'%');
        }

        if (!empty($filters['vendor'])) {
            $query->where('vendor', 'like', '%'.$filters['vendor'].'%');
        }

        if (!empty($filters['created_at'])) {
            $query->whereDate('created_at', $filters['created_at']);
        }

        if ($sort && in_array($sort, ['invoice', 'created_at', 'qty', 'price'])) {
            if ($sort === 'qty') {
                $query->orderBy(
                    Purchase_product::select('qty')->whereColumn('purchase_id', 'purchase.id')->limit(1),
                    $direction
                );
            } else {
                $query->orderBy($sort, $direction);
            }
        }

        $purchases = $query->paginate($per_page);
        return $purchases;
    }
}
