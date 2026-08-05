<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $table    = 'products';
    protected $fillable = ['cat_id', 'cat_name', 'name', 'description', 'has_variant', 'product_origin_id', 'marketplace_id'];

    public static function productUpsert(array $data)
    {
        return DB::transaction(function () use ($data) {
            $productData = [
                'cat_id'            => $data['cat_id'] ?? 0,
                'cat_name'          => $data['cat_name'] ?? null,
                'name'              => $data['name'] ?? null,
                'description'       => $data['description'] ?? null,
                'has_variant'       => $data['has_variant'] ?? 0,
                'product_origin_id' => $data['product_origin_id'] ?? 0,
                'marketplace_id'    => $data['marketplace_id'] ?? 0
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

                $variant_id = array_column($data['variants'], 'id');
                $sku_id     = array_column($data['variants'], 'sku_id');

                if (!empty($data['id']))
                {
                    Product_variant::where('product_id', $data['id'])->whereNotIn('id', $variant_id)->delete();
                    Product_sku::where('product_id', $data['id'])->whereNotIn('id', $sku_id)->delete();
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
                        'name'               => !empty($variant['sku']) ? $variant['sku'] : sprintf('%s-%s', self::prefixSku($data['name']), $variant['name']),
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

            $prefixSku      = self::prefixSku($data['name']) . '-' . $product->id ?? 0;
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

    // public function skus(): HasOne
    // {
    //     return $this->hasOne(Product_sku::class, 'product_id');
    // }

    public function skus(): HasMany
    {
        return $this->HasMany(Product_sku::class, 'product_id');
    }

    private static function prefixSku(string $text)
    {
        return collect(explode(' ', $text))->map(fn ($word) => strtolower(substr($word, 0, 1)))->implode('');
    }

    public static function productGet(int $id = 0, int $perPage = 15, array $filters = [], ?string $sort = null, string $direction = 'asc', bool $paginate = true)
    {
        $query = self::with(['category', 'variants.skus', 'skus']);

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        if (!empty($filters['category'])) {
            $query->where('cat_id', $filters['category']);
        }

        if ($sort && in_array($sort, ['name', 'price', 'stock', 'cat_name'])) {
            if ($sort === 'price' || $sort === 'stock') {
                $query->orderBy(
                    Product_sku::select($sort === 'price' ? 'original_price' : 'stock')
                        ->whereColumn('product_id', 'products.id')
                        ->limit(1),
                    $direction
                );
            } else {
                $query->orderBy($sort, $direction);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        if (empty($id)) {
            if ($paginate) {
                $products = $query->paginate($perPage)->through(function ($product) {

                    if ($product->has_variant) {

                        $product->items = $product->variants->map(function ($variant) {
                            return [
                                'id'         => $variant->id,
                                'product_id' => $variant->product_id,
                                'name'       => $variant->name,
                                'sku_id'     => $variant->skus?->id,
                                'sku'        => $variant->skus?->name,
                                'stock'      => $variant->skus?->stock,
                                'price'      => $variant->skus?->original_price,
                            ];
                        });

                    } else {

                        $product->items = collect([
                            [
                                'id'         => 0,
                                'product_id' => $product->id,
                                'name'       => $product->name,
                                'sku_id'     => $product->skus?->first()?->id,
                                'sku'        => $product->skus?->first()?->name,
                                'stock'      => $product->skus?->first()?->stock,
                                'price'      => $product->skus?->first()?->original_price,
                            ]
                        ]);
                    }

                    $product->unsetRelation('variants');
                    $product->unsetRelation('skus');

                    return $product;
                });
            } else {
                $products = $query->get();
            }
        } else {
            $products = $query->findOrFail($id);
            $products->items = collect();

            if ($products->has_variant)
            {
                $products->items = $products->variants->map(function($variant) {
                    return [
                        'id'         => $variant->id,
                        'product_id' => $variant->product_id,
                        'name'       => $variant->name,
                    'sku_id'     => $variant->skus?->id,
                    'sku'        => $variant->skus?->name,
                    'stock'      => $variant->skus?->stock,
                    'price'      => $variant->skus?->original_price,
                    ];
                });
                $products->unsetRelation('variants');
                $products->unsetRelation('skus');
            } else {
                $products->sku_id = $products->skus?->first()?->id;
                $products->sku    = $products->skus?->first()?->name;
                $products->price  = $products->skus?->first()?->original_price;
                $products->stock  = $products->skus?->first()?->stock;
            }
        }

        return $products;
    }

    public static function productOptions(): array
    {
        return self::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->values()
            ->map(fn ($value) => [
                'value' => $value,
                'label' => $value,
            ])
            ->all();
    }
}
