<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 10)
    {
        return Product::with(['category', 'sku', 'shop'])->where('is_active', true)->paginate($perPage);
    }

    public function searchProducts(string $query, int $perPage = 10)
    {
        return Product::search($query)
            ->query(fn($q) => $q->with('category', 'sku'))
            ->paginate($perPage);
    }

    public function getById(string $id)
    {
        return Product::with([
            'shop',
            'category',
            'sku.attributeOptions'
        ])->findOrFail($id);
    }

    public function createProduct(array $data)
    {
        $data['product_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        $data['is_active'] = true;

        return Product::create($data);
    }

    public function updateProduct(string $id, array $data)
    {
        $product = Product::findOrFail($id);
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        }

        $product->update($data);
        return $product;
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        return $product->delete();
    }

    public function bulkCreate(array $products)
    {
        $data = collect($products)->map(function ($product) {
            return [
                'product_id' => (string) Ulid::generate(),
                'shop_id' => $product['shop_id'],
                'category_id' => $product['category_id'],
                'brand_id' => $product['brand_id'] ?? null,
                'name' => $product['name'],
                'slug' => Str::slug($product['name']) . '-' . rand(100, 999),
                'description' => $product['description'] ?? null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();

        return Product::insert($data);
    }

    public function bulkDelete(array $productIds)
    {
        return Product::whereIn('product_id', $productIds)->delete();
    }
}
