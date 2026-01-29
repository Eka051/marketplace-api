<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $cartItems = $this->cartService->getUserCart($request->user()->user_id);

        return $this->successResponse(
            CartResource::collection($cartItems),
            'Cart retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku_id' => 'required|exists:product_skus,sku_id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = $this->cartService->addToCart(
            $request->user()->user_id,
            $request->input('sku_id'),
            $request->input('quantity')
        );

        return $this->successResponse(new CartResource($cart), 'Product added to cart');
    }

    public function destroy($id)
    {
        $this->cartService->removeFromCart($id);

        return $this->successResponse(null, 'Item successfully removed from cart');
    }
}
