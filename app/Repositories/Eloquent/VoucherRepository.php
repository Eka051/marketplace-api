<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\VoucherRepositoryInterface;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Carbon\Carbon;
use Exception;

class VoucherRepository implements VoucherRepositoryInterface
{
    public function create(array $data)
    {
        return Voucher::create($data);
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Voucher::query();

        // Filter by status (active/inactive)
        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->where('is_active', true);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by type (percentage/fixed)
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Search by code
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($perPage);
    }

    public function findByCode(string $code)
    {
        return Voucher::where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    public function getById(string $voucherId)
    {
        return Voucher::findOrFail($voucherId);
    }

    public function checkUserUsage(string $userId, string $voucherId)
    {
        return VoucherUsage::where('user_id', $userId)
            ->where('voucher_id', $voucherId)
            ->count();
    }

    public function decrementQuota(string $voucherId)
    {
        return Voucher::where('voucher_id', $voucherId)
            ->where('quota', '>', 0)
            ->decrement('quota');
    }

    public function createUsage(array $data)
    {
        return VoucherUsage::create($data);
    }

    public function incrementQuota(string $voucherId)
    {
        return Voucher::where('voucher_id', $voucherId)
            ->increment('quota');
    }

    public function validateVoucher(string $code, string $userId, int $totalPrice)
    {
        $voucher = $this->findByCode($code);

        if (!$voucher) {
            throw new Exception('Voucher not found');
        }

        // Check if voucher is active
        if (!$voucher->is_active) {
            throw new Exception('Voucher is not active');
        }

        // Check date validity
        $now = Carbon::now();
        if ($voucher->start_at && $now->lt($voucher->start_at)) {
            throw new Exception('Voucher is not yet available');
        }

        if ($voucher->end_at && $now->gt($voucher->end_at)) {
            throw new Exception('Voucher has expired');
        }

        // Check quota
        if ($voucher->quota != null && $voucher->quota <= 0) {
            throw new Exception('Voucher quota has been exhausted');
        }

        // Check user usage
        $usageCount = $this->checkUserUsage($userId, $voucher->voucher_id);
        if ($usageCount > 0) {
            throw new Exception('Voucher has already been used');
        }

        // Check minimum purchase
        if ($voucher->min_purchase && $totalPrice < $voucher->min_purchase) {
            throw new Exception(
                'Minimum purchase amount is IDR ' .
                    number_format($voucher->min_purchase, 0, ',', '.')
            );
        }

        return $voucher;
    }

    public function findUsageByOrderId(string $orderId)
    {
        return VoucherUsage::where('order_id', $orderId)->first();
    }

    public function deleteUsage(int $usageId)
    {
        return VoucherUsage::where('usage_id', $usageId)->delete();
    }

    public function getActiveVouchers(int $perPage = 10)
    {
        return Voucher::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('end_at')
                    ->orWhere('end_at', '>=', Carbon::now());
            })
            ->where(function ($query) {
                $query->whereNull('quota')
                    ->orWhere('quota', '>', 0);
            })
            ->paginate($perPage);
    }

    public function update(string $voucherId, array $data)
    {
        throw new Exception('Not implemented');
    }

    public function delete(string $voucherId)
    {
        throw new Exception('Not implemented');
    }
}
