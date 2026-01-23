<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $primaryKey = 'sku_id';

    protected $guarded = [
        'sku_id',
        'timestamps'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function attributeOptions()
    {
        return $this->belongsToMany(AttributeOption::class, 'sku_variant_options', 'sku_id', 'option_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'sku_id', 'sku_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'sku_id', 'sku_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'sku_id', 'sku_id');
    }
}
