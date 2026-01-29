<?php

namespace App\Services;

use App\Interfaces\Repositories\CartRepositoryInterface;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Repositories\ProductRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected $orderRepository;
    protected $cartRepository;
    protected $productRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, CartRepositoryInterface $cartRepository, ProductRepositoryInterface $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    public function checkout(string $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $cartItems = $this->cartRepository->getByUserId($userId);
            if ($cartItems->isEmpty()) {
                throw new Exception("Shopping cart is empty");
            }

            $totalPrice = 0;
            $orderItemsData = [];

            foreach ($cartItems as $item) {
                $sku = $this->productRepository->getSkuWithLock($item->sku_id);

                if ($sku->stock < $item->quantity) {
                    throw new Exception("{$sku->product->name} product stock is insufficient");
                }

                $subTotal = $sku->price * $item->quantity;
                $totalPrice += $subTotal;

                $orderItemsData[] = [
                    'sku_id' => $sku->sku_id,
                    'product_name' => $sku->product->name,
                    'sku_code' => $sku->sku_code,
                    'price' => $sku->price,
                    'weight' => $sku->weight,
                    'quantity' => $item->quantity,
                    'sub_total' => $subTotal,
                ];

                $this->productRepository->decrementStok($sku->sku_id, $item->quantity);
            }

            $shippingCost = $data['shipping_cost'] ?? 0;
            $order = $this->orderRepository->create([
                'order_id' => (string) Str::ulid(),
                'user_id' => $userId,
                'shop_id' => $data['shop_id'],
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'status' => 'unpaid',
                'total_price' => $totalPrice,
                'shipping_cost' => $shippingCost,
                'grand_total' => $totalPrice + $shippingCost,
                'note' => $data['note'] ?? null
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->order_id;
                $this->orderRepository->createItems($itemData);

                $this->productRepository->recordStockMovement([
                    'sku_id' => $itemData['sku_id'],
                    'type' => 'out',
                    'quantity' => $itemData['quantity'],
                    'reason' => 'Order ' . $order->order_number,
                    'order_id' => $order->order_id
                ]);
            }

            $this->cartRepository->clearByUserId($userId);

            return $order;
        });
    }
}
