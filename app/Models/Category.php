<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

/**
 * @property string $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $parent_id
 */
class Category extends Model
{
    protected $primaryKey = 'category_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
    ];

    public static function booted()
    {
        static::creating(function (Category $category){
            $category->category_id = (string) Ulid::generate();
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'category_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'category_id');
    }

    public function shop()
{
    return $this->belongsTo(Shop::class, 'shop_id', 'shop_id');
}
}
