<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Orders extends Model
{
    protected $fillable = [
        'invoice',
        'waybill',
        'marketplace_id',
        'buyer_user_id',
        'buyer_username',
        'buyer_phone',
        'buyer_address',
        'courier',
        'qty',
        'discount',
        'total_price',
        'status',
        'order_time',
        'payment_method',
        'notes',
    ];

    public function products()
    {
        return $this->hasMany(OrderProducts::class, 'order_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['invoice'] ?? null, function ($query, $invoice) {
            $query->where('invoice', 'like', '%' . $invoice . '%');
        })->when($filters['buyer_username'] ?? null, function ($query, $buyer_username) {
            $query->where('buyer_username', 'like', '%' . $buyer_username . '%');
        })->when($filters['courier'] ?? null, function ($query, $courier) {
            $query->where('courier', 'like', '%' . $courier . '%');
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['start_date'] ?? null, function ($query, $start_date) {
            $query->whereDate('order_time', '>=', $start_date);
        })->when($filters['end_date'] ?? null, function ($query, $end_date) {
            $query->whereDate('order_time', '<=', $end_date);
        });
    }

    public static function insertOrderFromShopee($data, $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $order = self::updateOrCreate(
                ['invoice' => $data['invoice']],
                $data
            );

            foreach ($items as $item) {
                OrderProducts::updateOrCreate(
                    [
                        'order_id'         => $order->id,
                        'product_model_id' => $item['product_model_id'],
                    ],
                    $item
                );
            }

            return $order;
        });
    }
}
