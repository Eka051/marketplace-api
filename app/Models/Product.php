<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\Ulid;

class Product extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'product_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [
        'product_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::creating(function (Product $product) {
            $product->product_id = (string) Ulid::generate();
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function sku()
    {
        return $this->hasMany(ProductSku::class, 'product_id', 'product_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'shop_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class, 'product_id', 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'product_id');
    }

    public function wishlists()
    {
        return $this->belongsToMany(Wishlist::class, 'wishlist_items', 'product_id', 'wishlist_id');
    }
}
