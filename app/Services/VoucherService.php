<?php

namespace App\Services;

use App\Interfaces\Repositories\VoucherRepositoryInterface;

class VoucherService
{
    protected $voucherRepo;

    public function __construct(VoucherRepositoryInterface $voucherRepo)
    {
        $this->voucherRepo = $voucherRepo;
    }

    public function calculateDiscount(string $code, string $userId, int $totalPrice): array
    {
        $voucher = $this->voucherRepo->validateVoucher($code, $userId, $totalPrice);

        $discount = 0;

        if ($voucher->type === 'percentage') {
            $discount = ($totalPrice * $voucher->value) / 100;

            if ($voucher->max_discount && $discount > $voucher->max_discount) {
                $discount = $voucher->max_discount;
            }
        } else {
            $discount = $voucher->value;
        }

        if ($discount > $totalPrice) {
            $discount = $totalPrice;
        }

        return [
            'voucher_id' => $voucher->voucher_id,
            'code' => $voucher->code,
            'type' => $voucher->type,
            'discount_amount' => (int) $discount,
            'final_price' => $totalPrice - $discount
        ];
    }

    public function applyVoucher(string $voucherId, string $userId, string $orderId, int $discountAmount)
    {
        $this->voucherRepo->decrementQuota($voucherId);

        return $this->voucherRepo->createUsage([
            'voucher_id' => $voucherId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_amount' => $discountAmount,
            'used_at' => now()
        ]);
    }

    public function refundVoucher(string $orderId)
    {
        $usage = $this->voucherRepo->findUsageByOrderId($orderId);

        if ($usage) {
            $this->voucherRepo->incrementQuota($usage->voucher_id);
            $this->voucherRepo->deleteUsage($usage->usage_id);
        }
    }

    public function getAvailableVouchers(int $perPage = 10)
    {
        return $this->voucherRepo->getActiveVouchers($perPage);
    }

    public function getVoucherById(string $id)
    {
        return $this->voucherRepo->getById($id);
    }

    public function validateVoucherCode(string $code, string $userId, int $totalPrice)
    {
        return $this->voucherRepo->validateVoucher($code, $userId, $totalPrice);
    }

    public function createVoucher(array $data)
    {
        return $this->voucherRepo->create($data);
    }

    public function updateVoucher(string $id, array $data)
    {
        return $this->voucherRepo->update($id, $data);
    }

    public function deleteVoucher(string $id)
    {
        return $this->voucherRepo->delete($id);
    }

    public function getAllVouchers(array $filters = [], int $perPage = 10)
    {
        return $this->voucherRepo->getAll($filters, $perPage);
    }
}
