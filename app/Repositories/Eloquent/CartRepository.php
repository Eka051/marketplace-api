<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\CartRepositoryInterface;
use App\Models\Cart;

class CartRepository implements CartRepositoryInterface
{
    public function getByUserId(string $userId)
    {
        return Cart::with(['sku.product'])
            ->where('user_id', $userId)
            ->get();
    }

    public function findItem(string $userId, string $skuId)
    {
        return Cart::where('user_id', $userId)
            ->where('sku_id', $skuId)
            ->first();
    }

    public function updateOrCreate(array $data)
    {
        $cart = Cart::where('user_id', $data['user_id'])
        ->where('sku_id', $data['sku_id'])
        ->first();

        if ($cart) {
            $cart->update([
                'quantity' => $cart->quantity + $data['quantity']
            ]);
            return $cart;
        }

        return Cart::create( $data);
    }

    public function deleteItem(int $cartId)
    {
        $cart = Cart::where('cart_id', $cartId);
        return $cart->delete();
    }

    public function clearByUserId(string $userId)
    {
        $cart = Cart::where('user_id', $userId);
        return $cart->delete();
    }
}
