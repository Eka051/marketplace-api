<?php

namespace App\Interfaces\Repositories;

interface PaymentRepositoryInterface
{
    public function create(array $data);
    public function findById(string $paymentId);
    public function findByExternalId(string $externalId);
    public function findByOrderId(string $orderId);
    public function update(string $paymentId, array $data);
    public function updateStatus(string $paymentId, string $status, ?string $paidAt = null);
    public function getByStatus(string $status);
    public function getPendingPayments();
    public function getSuccessPayments();
    public function getFailedPayments();
    public function getExpiredPayments();
    public function getByMethod(string $method);
    public function getUserPayments(string $userId);
    public function getUserPaymentHistory(string $userId, ?string $status = null);
    public function getShopPayments(string $shopId);
    public function getShopPaymentHistory(string $shopId, ?string $status = null);
    public function getAllPayments(?int $perPage = 15);
    public function getPaymentWithOrder(string $paymentId);
}
