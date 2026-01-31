<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $data)
    {
        return Order::create($data);
    }

    public function createItem(array $item)
    {
        return OrderItem::create($item);
    }

    public function createItems(array $items)
    {
        return OrderItem::insert($items);
    }

    public function findWithDetails(string $oderId)
    {
        $user = Auth::user();
        $shopId = $user->shop ? $user->shop->shop_id : null;

        return Order::with(['user', 'shop', 'items', 'shipment', 'latestStatus'])
            ->where('order_id', $oderId)
            ->where(function ($query) use ($user, $shopId) {
                $query->where('user_id', $user->user_id);
                if ($shopId) {
                    $query->orWhere('shop_id', $shopId);
                }
            })->firstOrFail();
    }

    public function getHistory(string $userId)
    {
        return Order::with(['items', 'shop', 'latestStatus'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function getShopHistory(string $shopId)
    {
        return Order::with(['items', 'shop', 'latestStatus'])
            ->where('shop_id', $shopId)
            ->latest()
            ->get();
    }

    public function updateStatus(string $orderId, string $status)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $status]);
        return $order;
    }

    public function findByOrderNumber(string $orderNumber)
    {
        return Order::where('order_number', $orderNumber)->firstOrFail();
    }
}
