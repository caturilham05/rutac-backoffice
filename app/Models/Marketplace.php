<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $table    = 'marketplaces';
    protected $fillable = [
        'marketplace',
        'store',
        'marketplace_id',
        'shop_id',
        'access_token',
        'refresh_token',
        'chiper',
        'token_expires_at',
        'refresh_token_expires_at',
        'app_key',
        'app_secret',
    ];

    public static function marketplaceUpsert($data = [])
    {
        return self::updateOrCreate(
            [
                'id' => $data['id'] ?? null
            ],
            [
                'marketplace' => $data['marketplace'],
                'store'       => $data['store'],
            ]
        );
    }
}
