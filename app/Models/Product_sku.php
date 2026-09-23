<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;

class Product_sku extends Model
{
    protected $table = 'product_skus';

    protected $fillable = ['product_id', 'product_variant_id', 'name', 'stock', 'original_price', 'discount_price', 'product_model_id'];

    /** @return Collection<int, self> */
    public static function searchShopeeCalculator(int $marketplaceId, string $query): Collection
    {
        return self::query()
            ->join('products', 'products.id', '=', 'product_skus.product_id')
            ->join('marketplaces', 'marketplaces.id', '=', 'products.marketplace_id')
            ->leftJoin('product_variants', function (JoinClause $join): void {
                $join->on('product_variants.id', '=', 'product_skus.product_variant_id')
                    ->on('product_variants.product_id', '=', 'products.id');
            })
            ->where('products.marketplace_id', $marketplaceId)
            ->where('marketplaces.marketplace', 'Shopee')
            ->where('product_skus.original_price', '>', 0)
            // ->where('product_skus.discount_price', '>', 0)
            ->where(function (Builder $builder) use ($query): void {
                $builder->where('products.name', 'like', '%'.$query.'%')
                    ->orWhere('product_variants.name', 'like', '%'.$query.'%')
                    ->orWhere('product_skus.name', 'like', '%'.$query.'%');
            })
            ->orderBy('products.name')->orderBy('product_skus.id')->limit(20)
            ->get(['product_skus.id as sku_id', 'products.id as product_id', 'products.name as product_name',
                'product_variants.name as variant_name', 'product_skus.name as sku',
                'product_skus.original_price', 'product_skus.discount_price']);
    }
}
