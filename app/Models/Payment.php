<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class Payment extends Model
{
    protected $primaryKey = 'payment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'payment_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::class(function (Payment $payment) {
            $payment->payment_id = (string) Ulid::generate();
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
