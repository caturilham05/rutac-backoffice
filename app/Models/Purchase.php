<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    protected $table    = 'purchases';
    protected $fillable = ['invoice', 'vendor', 'price', 'discount', 'additional_fee', 'purchase_date'];
    protected $appends  = ['created_at_formatted', 'purchase_date_formatted'];
    protected $casts    = [
        'purchase_date' => 'date',
    ];

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->translatedFormat('d F Y');
    }

    public function getPurchaseDateFormattedAttribute(): ?string
    {
        return $this->purchase_date?->translatedFormat('d F Y');
    }

    public static function purchaseInsert(array $data)
    {
        return DB::transaction(function () use ($data) {
            $price_total = 0;
            foreach ($data['products'] as $product) {
                $price_total += ($product['price'] * $product['qty']);
            }

            $purchase = self::create([
                'vendor'         => $data['vendor'],
                'invoice'        => $data['invoice'],
                'price'          => $price_total,
                'discount'       => $data['discount'],
                'additional_fee' => $data['additional_fee'],
                'purchase_date'  => $data['purchase_date'],
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

        if (!empty($filters['purchase_date'])) {
            $query->whereDate('purchase_date', $filters['purchase_date']);
        }

        if ($sort && in_array($sort, ['invoice', 'purchase_date', 'qty', 'price'])) {
            if ($sort === 'qty') {
                $query->orderBy(
                    Purchase_product::select('qty')->whereColumn('purchase_id', 'purchase.id')->limit(1),
                    $direction
                );
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $purchases = $query->paginate($per_page);
        return $purchases;
    }
}
