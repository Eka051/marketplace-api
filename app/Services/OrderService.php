<?php

namespace App\Services;

use App\Interfaces\Repositories\CartRepositoryInterface;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Repositories\ProductRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class OrderService
{
    protected $orderRepository;
    protected $cartRepository;
    protected $productRepository;
    protected $voucherService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        VoucherService $voucherService
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->$voucherService = $voucherService;
    }

    public function getHistory()
    {
        $user = Auth::user();

        if ($user->role === 'seller' && $user->shop) {
            return $this->orderRepository->getShopHistory($user->shop->shop_id);
        }

        return $this->orderRepository->getHistory($user->user_id);
    }

    public function getOrderDetail(string $orderId)
    {
        return $this->orderRepository->findWithDetails($orderId);
    }

    public function checkoutFromCart(string $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            $cartItems = $this->cartRepository->getByUserId($userId);
            
            if ($cartItems->isEmpty()) {
                throw new Exception("Shopping cart is empty");
            }

            $skuIds = $cartItems->pluck('sku_id')->toArray();
            $skus = $this->productRepository->getSkusWithLock($skuIds);

            $totalPrice = 0;
            $orderItemsData = [];

            foreach ($cartItems as $item) {
                $sku = $skus->get($item->sku_id);

                if (!$sku) {
                    throw new Exception("Product SKU not found");
                }

                if ($sku->stock < $item->quantity) {
                    throw new Exception("Insufficient stock for {$sku->product->name}");
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

                $this->productRepository->decrementStock($sku->sku_id, $item->quantity);
            }

            $order = $this->createOrder($userId, $data, $totalPrice, $orderItemsData);
            $this->cartRepository->clearByUserId($userId);

            return $order;
        });
    }

    public function checkoutDirect(string $userId, array $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            if (!isset($data['items']) || empty($data['items'])) {
                throw new Exception("Order items are required");
            }

            $skuIds = array_column($data['items'], 'sku_id');
            $skus = $this->productRepository->getSkusWithLock($skuIds);

            $totalPrice = 0;
            $orderItemsData = [];

            foreach ($data['items'] as $item) {
                if (!isset($item['sku_id']) || !isset($item['quantity'])) {
                    throw new Exception("Invalid item data");
                }

                $sku = $skus->get($item['sku_id']);

                if (!$sku) {
                    throw new Exception("Product SKU not found");
                }

                if ($sku->stock < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$sku->product->name}");
                }

                $subTotal = $sku->price * $item['quantity'];
                $totalPrice += $subTotal;

                $orderItemsData[] = [
                    'sku_id' => $sku->sku_id,
                    'product_name' => $sku->product->name,
                    'sku_code' => $sku->sku_code,
                    'price' => $sku->price,
                    'weight' => $sku->weight,
                    'quantity' => $item['quantity'],
                    'sub_total' => $subTotal,
                ];

                $this->productRepository->decrementStock($sku->sku_id, $item['quantity']);
            }

            return $this->createOrder($userId, $data, $totalPrice, $orderItemsData);
        });
    }

    protected function createOrder(string $userId, array $data, float $totalPrice, array $orderItemsData)
    {
        $shippingCost = $data['shipping_cost'] ?? 0;
        $discountAmount = 0;
        $voucherData = null;

        if (isset($data['voucher_code']) && !isEmpty($data['voucher_code'])) {
            $voucherData = $this->voucherService->calculateDiscount(
                $data['voucher_code'],
                $userId,
                $totalPrice
            );
            $discountAmount = $voucherData['discount_amount'];
        }

        $grandTotal = $totalPrice + $shippingCost - $discountAmount;
        
        $order = $this->orderRepository->create([
            'order_id' => (string) Str::ulid(),
            'user_id' => $userId,
            'shop_id' => $data['shop_id'],
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'status' => 'unpaid',
            'total_price' => $totalPrice,
            'shipping_cost' => $shippingCost,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
            'note' => $data['note'] ?? null
        ]);

        foreach ($orderItemsData as $itemData) {
            $itemData['order_id'] = $order->order_id;
            $this->orderRepository->createItem($itemData);

            $this->productRepository->recordStockMovement([
                'sku_id' => $itemData['sku_id'],
                'type' => 'out',
                'quantity' => $itemData['quantity'],
                'reason' => 'Order ' . $order->order_number,
                'order_id' => $order->order_id
            ]);
        }

        if ($voucherData) {
            $this->voucherService->applyVoucher(
                $voucherData['voucher_id'],
                $userId,
                $order->order_id,
                $discountAmount
            );
        }

        return $order;
    }

    public function checkout(string $userId, array $data)
    {
        $checkoutType = $data['checkout_type'] ?? 'cart';

        if ($checkoutType === 'direct') {
            return $this->checkoutDirect($userId, $data);
        }

        return $this->checkoutFromCart($userId, $data);
    }
}
