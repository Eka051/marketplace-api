<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $primaryKey = 'movement_id';

    protected $guarded = [
        'movement_id',
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
}
