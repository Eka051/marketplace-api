<?php

namespace App\Interfaces\Repositories;

interface ProductRepositoryInterface {
    public function getAll(int $perPage = 10);
    public function searchProducts(string $query, int $perPage = 10);
    public function getById(string $id);
    public function createProduct(array $data);
    public function updateProduct(string $id, array $data);
    public function deleteProduct(string $id);
    public function bulkCreate(array $products);
    public function bulkDelete(array $productIds);
    public function getBulkByIds(array $productIds);
    public function findSkuById(int $skuId);
    public function getSkuWithLock(int $skuId);
    public function decrementStok(int $skuId, int $quantity);
    public function recordStockMovement(array $data);
    public function createProductImage(array $data);
    public function getProductImagesCount(string $productId);
}