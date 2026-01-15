<?php

namespace App\Interfaces\Repositories;

interface BrandRepositoryInterface {
    public function create(array $data);
    public function bulkCreate(array $brands);
    public function findByName(string $name);
    public function getAll();
    public function getById(string $id);
    public function update(string $id, array $data);
    public function delete(string $id);
}