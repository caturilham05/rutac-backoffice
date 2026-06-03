<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'operational',
        'marketplace_id',
    ];

    public static function shopeeFeeAction($data = [])
    {
        return self::updateOrCreate(
            [
                'id' => $data['id'] ?? null
            ],
            [
                'admin_fee'      => $data['admin_fee'],
                'free_shipping'  => $data['free_shipping'],
                'extra_promo'    => $data['extra_promo'],
                'affiliate'      => $data['affiliate'],
                'live'           => $data['live'],
                'premi_fee'      => $data['premi_fee'],
                'operational'    => $data['operational'],
                'processing_fee' => $data['processing_fee'],
                'marketplace_id' => $data['marketplace_id'],
            ]
        );
    }

    /**
     * Get the user associated with the ConfigFee
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function marketplace(): HasOne
    {
        return $this->hasOne(Marketplace::class, 'id', 'marketplace_id');
    }
}
