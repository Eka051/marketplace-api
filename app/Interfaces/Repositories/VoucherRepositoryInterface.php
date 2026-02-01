<?php

namespace App\Interfaces\Repositories;

interface VoucherRepositoryInterface {
    public function findByCode(string $code);
    public function getById(string $voucherId);
    public function checkUserUsage(string $userId, string $voucherId);
    public function decrementQuota(string $voucherId);
    public function createUsage(array $data);
    public function incrementQuota(string $voucherId);
    public function validateVoucher(string $code, string $userId, int $totalPrice);
    public function findUsageByOrderId(string $orderId);
    public function deleteUsage(int $usageId);
    public function getActiveVouchers(int $perPage = 10);
}