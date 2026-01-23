<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\StockMovement;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll(int $perPage = 10)
    {
        return Product::with([
            'category',
            'skus.attributeOptions',
            'shop',
            'brand',
            'attributes',
            'images' => function ($query) {
                $query->orderBy('position');
            },
            'reviews'
        ])->where('is_active', true)->paginate($perPage);
    }

    public function searchProducts(string $query, int $perPage = 10)
    {
        return Product::search($query)
            ->query(fn($q) => $q->with('category', 'skus'))
            ->paginate($perPage);
    }

    public function getById(string $id)
    {
        return Product::with([
            'shop',
            'category',
            'skus.attributeOptions',
            'brand',
            'attributes',
            'images' => function ($query) {
                $query->orderBy('position');
            },
            'reviews',
            'wishlists.user'
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

    public function getBulkByIds(array $productIds)
    {
        return Product::whereIn('product_id', $productIds)->with([
            'category',
            'skus.attributeOptions',
            'brand',
            'attributes',
            'images' => function ($query) {
                $query->orderBy('position');
            },
        ])->get();
    }

    public function findSkuById(int $skuId)
    {
        return ProductSku::with('product')->findOrFail($skuId);
    }

    public function getSkuWithLock(int $skuId)
    {
        return ProductSku::with('product')
            ->where('sku_id', $skuId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function decrementStok(int $skuId, int $quantity)
    {
        return ProductSku::where('sku_id', $skuId)->decrement('stock', $quantity);
    }

    public function recordStockMovement(array $data)
    {
        return StockMovement::create($data);
    }
}
