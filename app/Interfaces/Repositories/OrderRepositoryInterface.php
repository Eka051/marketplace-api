<?php

namespace App\Interfaces\Repositories;

interface OrderRepositoryInterface {
    public function create(array $data);
    public function createItems(array $items);
    public function findWithDetails(string $oderId);
    public function getHistory(string $userId);
    public function updateStatus(string $orderId, string $status);
    public function findByOrderNumber(string $orderNumber);
    public function getShopHistory(string $shopId);
}