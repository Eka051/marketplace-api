<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Models\Product;

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
        return Product::create($data);
    }

    public function updateProduct(string $id, array $data)
    {
        $product = Product::findOrFail($id);
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
        return Product::insert($products);
    }

    public function bulkDelete(array $productIds)
    {
        return Product::whereIn('product_id', $productIds)->delete();
    }
}
