<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigFee extends Model
{
    protected $table    = 'config_fees';
    protected $fillable = [
        'admin_fee',
        'free_shipping',
        'extra_promo',
        'processing_fee',
        'affiliate',
        'live',
        'premi_fee',
        'operational'
    ];

    public static function shopeeFeeAction($value)
    {
        $setting = self::first();
        if (!$setting) {
            return self::create($value);
        }
        return $setting->update($value);
    }
}
