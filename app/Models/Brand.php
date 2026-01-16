<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

/**
 * @property string $brand_id
 * @property string $name
 * @property string $slug
 */
class Brand extends Model
{
    protected $primaryKey = 'brand_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [
        'brand_id',
        'timestamps'
    ];

    public static function booted()
    {
        static::creating(function (Brand $brand) {
            $brand->brand_id = (string) Ulid::generate();
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id', 'brand_id');
    }
}
