<?php

namespace App\Interface\Repositories;

interface ProducRepositoryInterface {
    public function getAllActive();
    public function getById(string $id);
    public function createProduct(array $data);
    public function updateProduct($id);
    public function deleteProduct($id);
}