<?php

namespace App\Interfaces\Repositories;

interface VoucherRepositoryInterface {
    public function findByCode(string $code);
    public function checkUserUsage(string $userId, string $voucherId);
    public function decrementQuota(string $voucherId);
    public function createUsage(array $data);
    public function incrementQuota(string $voucherId);
    public function getById(string $voucherId);
    public function validateVoucher(string $code, string $userId, int $totalPrice);
}