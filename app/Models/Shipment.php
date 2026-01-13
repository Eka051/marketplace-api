<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class Shipment extends Model
{
    protected $primaryKey = 'shipment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'shipment_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::class(function (Shipment $shipment) {
            $shipment->shipment_id = (string) Ulid::generate();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
