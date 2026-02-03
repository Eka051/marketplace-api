<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $orderService;
    protected $paymentService;

    public function __construct(OrderService $orderService, PaymentService $paymentService)
    {
        $this->orderService = $orderService;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        try {
            $orders = $this->orderService->getHistory();
            return $this->successResponse($orders, 'Orders retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function show(string $orderId)
    {
        try {
            $order = $this->orderService->getOrderDetail($orderId);
            return $this->successResponse($order, 'Order detail retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'checkout_type' => 'in:cart,direct',
            'shop_id' => 'required|string',
            'shipping_cost' => 'numeric|min:0',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:bank_transfer,e_wallet,credit_card,all',
            'items' => 'required_if:checkout_type,direct|array',
            'items.*.sku_id' => 'required_with:items|integer|exists:product_skus,sku_id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        try {
            $userId = $request->user()->id;
            $order = $this->orderService->checkout($userId, $request->all());

            // Create payment with Midtrans
            $payment = $this->paymentService->createPayment([
                'order_id' => $order->order_id,
                'method' => $request->payment_method,
                'amount' => $order->grand_total
            ]);
            
            return $this->successResponse([
                'order' => $order,
                'payment' => $payment,
                'payment_url' => $payment->payment_url
            ], 'Checkout successful', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
