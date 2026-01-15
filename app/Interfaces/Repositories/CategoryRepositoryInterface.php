<?php

namespace App\Interfaces\Repositories;

interface CategoryRepositoryInterface {
    public function create(array $data);
    public function bulkCreate(array $categories);
    public function findByNameAndShop(string $name, string $shopId);
    public function getAll();
    public function getById(string $id);
    public function update(string $id, array $data);
    public function delete(string $id);
}