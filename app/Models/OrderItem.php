<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $primaryKey = 'item_id';

    protected $guarded = [
        'item_id',
        'timestamps'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'sku_id', 'sku_id');
    }

    public function productReview()
    {
        return $this->hasOne(ProductReview::class, 'order_item_id', 'item_id');
    }
}
