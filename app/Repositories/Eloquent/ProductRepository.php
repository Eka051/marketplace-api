<?php

namespace App\Repositories\Eloquent;

use App\Interface\Repositories\ProducRepositoryInterface;
use App\Models\Product;

class ProductRepository implements ProducRepositoryInterface {
    public function getAllActive()
    {
        return Product::with(['category','sku', 'shop'])->where('is_active', true)->get();
    }

    public function getById(string $id)
    {
        return Product::with([
            'shop',
            'category',
            'sku.variantOption.attribute'
        ])->findOrFail($id);
    }

    public function createProduct(array $data)
    {
        throw new \Exception('Not implemented');
    }

    public function updateProduct($id)
    {
        throw new \Exception('Not implemented');
    }

    public function deleteProduct($id)
    {
        throw new \Exception('Not implemented');
    }
}
