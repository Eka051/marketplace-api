<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 * schema="Product",
 * type="object",
 * title="Product",
 * required={"name", "price", "sku"},
 * @OA\Property(property="id", type="integer", format="int64", description="Product ID"),
 * @OA\Property(property="name", type="string", description="Product name"),
 * @OA\Property(property="description", type="string", description="Product description"),
 * @OA\Property(property="price", type="integer", description="Product price"),
 * @OA\Property(property="sku", type="string", description="Stock Keeping Unit"),
 * @OA\Property(property="image", type="string", nullable=true, description="Product image URL"),
 * @OA\Property(property="category_id", type="integer", nullable=true, description="Category ID"),
 * @OA\Property(property="user_id", type="string", format="uuid", nullable=true, description="User ID"),
 * @OA\Property(property="is_active", type="boolean", description="Product status"),
 * @OA\Property(property="stock", type="integer", description="Product stock"),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp")
 * )
 *
 * @OA\Schema(
 * schema="StoreProductRequest",
 * type="object",
 * title="Store Product Request",
 * required={"name", "description", "price", "sku"},
 * @OA\Property(property="name", type="string"),
 * @OA\Property(property="description", type="string"),
 * @OA\Property(property="price", type="integer"),
 * @OA\Property(property="sku", type="string"),
 * @OA\Property(property="category_id", type="integer"),
 * @OA\Property(property="stock", type="integer")
 * )
 *
 * @OA\Schema(
 * schema="UpdateProductRequest",
 * type="object",
 * title="Update Product Request",
 * @OA\Property(property="name", type="string"),
 * @OA\Property(property="description", type="string"),
 * @OA\Property(property="price", type="integer"),
 * @OA\Property(property="sku", type="string"),
 * @OA\Property(property="category_id", type="integer"),
 * @OA\Property(property="stock", type="integer")
 * )
 */

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sku',
        'image',
        'category_id',
        'user_id',
        'is_active',
        'stock',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
