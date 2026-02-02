<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\PaymentRepositoryInterface;
use App\Models\Payment;

class PaymentRepository implements PaymentRepositoryInterface {
    public function create(array $data)
    {
        return Payment::create($data);
    }

    public function findById(string $paymentId)
    {
        return Payment::with('order')->where('payment_id', $paymentId)->firstOrFail();
    }

    public function findByExternalId(string $externalId)
    {
        return Payment::with('order')->where('external_id', $externalId)->firstOrFail();
    }

    public function findByOrderId(string $orderId)
    {
        return Payment::with('order')->where('order_id', $orderId)->firstOrFail();
    }

    public function update(string $paymentId, array $data)
    {
        $payment = Payment::where('payment_id', $paymentId)->firstOrFail();
        $payment->update($data);
        return $payment;
    }

    public function updateStatus(string $paymentId, string $status, ?string $paidAt = null)
    {
        $data = ['status' => $status];
        if ($paidAt) {
            $data['paid_at'] = $paidAt;
        } elseif ($status === 'success') {
            $data['paid_at'] = now();
        }
        
        $payment = Payment::where('payment_id', $paymentId)->firstOrFail();
        $payment->update($data);
        return $payment;
    }

    public function getByStatus(string $status)
    {
        return Payment::with(['order.user', 'order.shop'])
            ->where('status', $status)
            ->latest()
            ->get();
    }

    public function getPendingPayments()
    {
        return Payment::with(['order.user', 'order.shop'])
            ->where('status', 'pending')
            ->latest()
            ->get();
    }
    
    public function getSuccessPayments()
    {
        return Payment::with(['order.user', 'order.shop'])
            ->where('status', 'success')
            ->latest()
            ->get();
    }

    public function getFailedPayments()
    {
        return Payment::with(['order.user', 'order.shop'])
            ->where('status', 'failed')
            ->latest()
            ->get();
    }

    public function getExpiredPayments()
    {
        return Payment::with(['order.user', 'order.shop'])
            ->where('status', 'expired')
            ->latest()
            ->get();
    }

    public function getByMethod(string $method)
    {
        return Payment::with(['order.user', 'order.shop'])
            ->where('method', $method)
            ->latest()
            ->get();
    }

    public function getUserPayments(string $userId)
    {
        return Payment::with(['order.shop'])
            ->whereHas('order', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->get();
    }

    public function getUserPaymentHistory(string $userId, ?string $status = null)
    {
        $query = Payment::with(['order.shop'])
            ->whereHas('order', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
            
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->latest()->get();
    }

    public function getShopPayments(string $shopId)
    {
        return Payment::with(['order.user'])
            ->whereHas('order', function($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->latest()
            ->get();
    }

    public function getShopPaymentHistory(string $shopId, ?string $status = null)
    {
        $query = Payment::with(['order.user'])
            ->whereHas('order', function($q) use ($shopId) {
                $q->where('shop_id', $shopId);
            });
            
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->latest()->get();
    }

    public function getAllPayments(?int $perPage = 15)
    {
        return Payment::with(['order.user', 'order.shop'])
            ->latest()
            ->paginate($perPage);
    }

    public function getPaymentWithOrder(string $paymentId)
    {
        return Payment::with(['order.user', 'order.shop', 'order.items'])
            ->where('payment_id', $paymentId)
            ->firstOrFail();
    }
}