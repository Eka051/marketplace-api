<?php

namespace App\Interfaces\Repositories;

interface VoucherRepositoryInterface 
{
    public function create(array $data);
    public function update(string $voucherId, array $data);
    public function delete(string $voucherId);
    public function findByCode(string $code);
    public function getById(string $voucherId);
    public function getAll(array $filters = [], int $perPage = 15);
    public function getActiveVouchers(int $perPage = 10);
    public function checkUserUsage(string $userId, string $voucherId);
    public function decrementQuota(string $voucherId);
    public function incrementQuota(string $voucherId);
    public function createUsage(array $data);
    public function findUsageByOrderId(string $orderId);
    public function deleteUsage(int $usageId);
    public function validateVoucher(string $code, string $userId, int $totalPrice);
}