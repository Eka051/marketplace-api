<?php

namespace App\Interfaces\Repositories;

interface CategoryRepositoryInterface {
    public function create(array $data);
    public function bulkCreate(array $categories);
    public function findByName(string $name);
    public function getAll(int $perPage);
    public function getById(string $id);
    public function update(string $id, array $data);
    public function delete(string $id);
}