<?php

namespace App\Services;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ProductService
{
    protected $productRepo;

    public function __construct(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function validateProductData(array $data, bool $isCreate = true)
    {
        $rules = [
            'shop_id' => 'required|string',
            'brand_id' => 'required|string',
            'category_id' => 'nullable|string',
            'name' => 'required|string|min:5|max:255',
            'description' => 'nullable|string',
        ];

        if ($isCreate) {
            $rules['shop_id'] .= '|exists:shops,shop_id';
            $rules['category_id'] .= '|exists:categories,category_id';
        }

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function getProducts(int $perPage = 15)
    {
        return $this->productRepo->getAll($perPage);
    }

    public function searchProducts(string $query, int $perPage = 10)
    {
        if (empty($query)) {
            throw new InvalidArgumentException('Query cannot be empty');
        }

        $products = $this->productRepo->searchProducts($query, $perPage);

        return $products;
    }

    public function createProduct(array $data)
    {
        $this->validateProductData($data, false);
        return $this->productRepo->createProduct($data);
    }

    public function updateData(string $id, array $data)
    {
        $this->validateProductData($data, false);
        return $this->productRepo->updateProduct($id, $data);
    }

    public function addMultipleProducts(array $products)
    {
        foreach ($products as $product) {
            $this->validateProductData($product);
        }
        return DB::transaction(function () use ($products) {
            return $this->productRepo->bulkCreate($products);
        });
    }

    public function deleteMultipleProducts(array $productIds)
    {
        return $this->productRepo->bulkDelete($productIds);
    }
}
