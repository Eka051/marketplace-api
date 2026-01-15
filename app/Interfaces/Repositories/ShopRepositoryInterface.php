<?php

namespace App\Interfaces\Repositories;

interface ShopRepositoryInterface
{
    public function searchShops(string $query, int $perPage = 10);
    public function getById(string $id);
    public function createShop(array $data);
    public function updateShop(string $id, array $data);
    public function deleteShop(string $id);
}
