<?php

namespace App\Interfaces\Repositories;

interface CartRepositoryInterface {
    public function getByUserId(string $userId);
    public function findItem(string $userId, string $skuId);
    public function updateOrCreate(array $data);
    public function deleteItem(int $cartId);
    public function clearByUserId(string $userId);
}