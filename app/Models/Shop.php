<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Uid\Ulid;

class Shop extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'shop_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'shop_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::creating(function (Shop $shop) {
            $shop->shop_id = (string) Ulid::generate();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'shop_id', 'shop_id');
    }

    public function reviews()
    {
        return $this->hasMany(ShopReview::class, 'shop_id', 'shop_id');
    }
}
