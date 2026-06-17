<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $table    = 'products';
    protected $fillable = ['cat_id', 'cat_name', 'name', 'description', 'has_variant'];

    public static function productUpsert(array $data)
    {
        return DB::transaction(function () use ($data) {
            $productData = [
                'cat_id'      => $data['cat_id'] ?? 0,
                'cat_name'    => $data['cat_name'] ?? null,
                'name'        => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'has_variant' => $data['has_variant'] ?? 0,
            ];

            $product = self::updateOrCreate(
                ['id' => $data['id'] ?? null],
                $productData
            );

            if (!empty($data['variants']))
            {
                if (!is_array($data['variants']))
                {
                    throw new \Exception('data tidak valid', 400);
                }

                foreach ($data['variants'] as $variant) {
                    $productVariantData = [
                        'product_id' => $product->id ?? 0,
                        'name'       => $variant['name']
                    ];

                    $productVariantInsert = Product_variant::updateOrCreate(
                        ['id' => $variant['id'] ?? null],
                        $productVariantData
                    );

                    $productSkuData = [
                        'product_id'         => $product->id ?? 0,
                        'product_variant_id' => $productVariantInsert->id ?? 0,
                        'name'               => !empty($variant['sku']) ? $variant['sku'] : sprintf('%s-%s', strtolower($data['name']), $variant['name']),
                        'stock'              => $variant['stock'],
                        'original_price'     => $variant['price']
                    ];

                    $productSkuInsert = Product_sku::updateOrCreate(
                        ['id' => $variant['sku_id'] ?? null],
                        $productSkuData
                    );
                }

                return $product ?? [];
            }


            $prefix = collect(explode(' ', $data['name']))
                ->map(fn ($word) => strtolower(substr($word, 0, 1)))
                ->implode('');

            $prefixSku      = $prefix . '-' . $product->id ?? 0;
            $productSkuData = [
                'product_id'         => $product->id ?? 0,
                'product_variant_id' => 0,
                'name'               => !empty($data['sku']) ? $data['sku'] : $prefixSku,
                'stock'              => $data['stock'],
                'original_price'     => $data['price'],
            ];

            $productSkuInsert = Product_sku::updateOrCreate(
                ['id' => $data['sku_id'] ?? null],
                $productSkuData
            );

            return $product;
        });
    }

    /**
     * Get the user associated with the ConfigFee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(Product_category::class, 'id', 'cat_id');
    }


    public function variants(): HasMany
    {
        return $this->hasMany(Product_variant::class, 'product_id');
    }

    public function skus(): HasMany
    {
        return $this->hasMany(Product_sku::class, 'product_id');
    }
}
