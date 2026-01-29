<?php

namespace App\Services;

use App\Interfaces\Repositories\CartRepositoryInterface;
use App\Interfaces\Repositories\ProductRepositoryInterface;
use Exception;

class CartService
{
    protected $cartRepository;
    protected $productRepository;

    public function __construct(CartRepositoryInterface $cartRepository, ProductRepositoryInterface $productRepository)
    {
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
    }

    public function getUserCart(string $userId)
    {
        return $this->cartRepository->getByUserId($userId);
    }

    public function addToCart(string $userId, int $skuId, int $quantity)
    {
        $sku = $this->productRepository->findSkuById($skuId);
        if (!$sku || $sku->stock < $quantity) {
            throw new Exception("Insufficient stock or product not found");
        }

        $existingItem = $this->cartRepository->findItem($userId, $skuId);

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($sku->stock < $newQuantity) {
                throw new Exception("Stock exceeds available stock");
            }

            return $this->cartRepository->updateOrCreate([
                'cart_id' => $existingItem->cart_id,
                'quantity' => $newQuantity
            ]);
        }

        return $this->cartRepository->updateOrCreate([
            'user_id' => $userId,
            'sku_id' => $skuId,
            'quantity' => $quantity
        ]);
    }

    public function removeFromCart(int $cartId)
    {
        return $this->cartRepository->deleteItem($cartId);
    }
}
