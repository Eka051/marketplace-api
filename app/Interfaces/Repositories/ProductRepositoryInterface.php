<?php

namespace App\Interfaces\Repositories;

interface ProductRepositoryInterface {
    public function getAll(int $perPage = 10);
    public function searchProducts(string $query, int $perPage = 10);
    public function getById(string $id);
    public function createProduct(array $data);
    public function updateProduct(string $id, array $data);
    public function deleteProduct($id);
    public function bulkCreate(array $products);
    public function bulkDelete(array $productIds);
}