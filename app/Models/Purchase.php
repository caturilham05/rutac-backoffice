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
}
