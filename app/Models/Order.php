<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class Order extends Model
{
    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'order_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::creating(function (Order $order) {
            $order->order_id = (string) Ulid::generate();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'shop_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id', 'order_id');
    }

    // Helper to get latest success payment
    public function latestPayment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id')->latestOfMany();
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'order_id', 'order_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'order_id', 'order_id');
    }

    public function voucherUsage()
    {
        return $this->hasOne(VoucherUsage::class, 'order_id', 'order_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id', 'order_id');
    }

    public function latestStatus()
    {
        return $this->hasOne(OrderStatusHistory::class, 'order_id', 'order_id')->latestOfMany();
    }
}
