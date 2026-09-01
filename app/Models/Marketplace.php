<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $table = 'marketplaces';

    protected $fillable = [
        'marketplace',
        'store',
        'shop_id',
        'access_token',
        'refresh_token',
        'chiper',
        'token_expires_at',
        'refresh_token_expires_at',
        'app_secret',
    ];

    public static function marketplaceUpsert($data = [])
    {
        if (! empty($data['id'])) {
            $condition = [
                'id' => $data['id'],
            ];

            unset($data['id']);
        } else {
            $condition = ['id' => null];
        }

        return self::updateOrCreate($condition, $data);
    }

    public static function marketplacePagination(int $per_page = 15, array $filters = [], ?string $sort = null, string $direction = 'asc')
    {
        $query = self::query()->select([
            'id',
            'marketplace',
            'store',
            'shop_id',
            'access_token',
            'refresh_token',
            'chiper',
            'token_expires_at',
            'refresh_token_expires_at',
            'app_secret',
        ]);

        if (! empty($filters['marketplace'])) {
            $query->where('marketplace', $filters['marketplace']);
        }

        if (! empty($filters['store'])) {
            $query->where('store', $filters['store']);
        }

        if ($sort && in_array($sort, ['marketplace', 'store'])) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query->paginate($per_page);
    }

    public static function marketplaceOptions(string $column): array
    {
        if (! in_array($column, ['marketplace', 'store'])) {
            return [];
        }

        return self::query()
            ->select($column)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->filter()
            ->values()
            ->map(fn ($value) => [
                'value' => $value,
                'label' => $value,
            ])
            ->all();
    }
}
