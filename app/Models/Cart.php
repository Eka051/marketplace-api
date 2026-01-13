<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $primaryKey = 'cart_id';

    protected $guarded = [
        'cart_id',
        'timestamps'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'sku_id', 'sku_id');
    }
}
