<?php

namespace App\Services;

use App\Interfaces\Repositories\PaymentRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Exception;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    protected $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
        $this->configureMidtrans();
    }

    /**
     * Configure Midtrans
     */
    protected function configureMidtrans()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        if (empty(Config::$serverKey) || empty(Config::$clientKey)) {
            throw new Exception('Midtrans configuration is missing. Please set in .env file!');
        }
    }

    /**
     * Create payment with Midtrans Snap
     */
    public function createPayment(array $data)
    {
        $this->validateCreatePaymentData($data);

        $midtransParams = $this->buildMidtransParams($data);

        try {
            $snapResponse = Snap::createTransaction($midtransParams);

            $paymentData = [
                'payment_id' => (string) Str::ulid(),
                'order_id' => $data['order_id'],
                'external_id' => $snapResponse->token,
                'method' => $data['method'],
                'amount' => $data['amount'],
                'status' => 'pending',
                'payment_url' => $snapResponse->redirect_url,
                'expired_at' => now()->addHours(24),
                'snap_token' => $snapResponse->token,
            ];

            return $this->paymentRepository->create($paymentData);
        } catch (Exception $e) {
            throw new Exception('Failed to create Midtrans payment: ' . $e->getMessage());
        }
    }

    /**
     * Build parameters for Midtrans Snap
     */
    protected function buildMidtransParams(array $data): array
    {
        $order = $this->getOrderDetails($data['order_id']);

        return [
            'transaction_details' => [
                'order_id' => $data['order_id'],
                'gross_amount' => (int) $data['amount'],
            ],
            'customer_details' => [
                'first_name' => $order->user->name ?? 'Customer',
                'email' => $order->user->email ?? '',
                'phone' => $order->user->phone ?? '',
            ],
            'item_details' => $this->buildItemDetails($order),
            'enabled_payments' => $this->getEnabledPayments($data['method']),
            'callbacks' => [
                'finish' => config('app.url') . '/payment/finish',
                'error' => config('app.url') . '/payment/error',
                'pending' => config('app.url') . '/payment/pending',
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => 24,
            ],
        ];
    }

    /**
     * Build item details for Midtrans
     */
    protected function buildItemDetails($order): array
    {
        $items = [];

        if (isset($order->items) && is_iterable($order->items)) {
            foreach ($order->items as $item) {
                $items[] = [
                    'id' => $item->sku_id ?? 'item-' . ($item->id ?? uniqid()),
                    'price' => (int) ($item->price ?? 0),
                    'quantity' => (int) ($item->quantity ?? 1),
                    'name' => substr($item->product->name ?? $item->name ?? 'Product', 0, 50),
                ];
            }
        }

        if (isset($order->shipping_cost) && $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping Cost',
            ];
        }

        if (isset($order->discount_amount) && $order->discount_amount > 0) {
            $items[] = [
                'id' => 'discount',
                'price' => -(int) $order->discount_amount,
                'quantity' => 1,
                'name' => 'Discount',
            ];
        }

        // Ensure at least one item exists
        if (empty($items)) {
            $items[] = [
                'id' => 'order',
                'price' => (int) ($order->grand_total ?? $order->total ?? 1),
                'quantity' => 1,
                'name' => 'Order Payment',
            ];
        }

        return $items;
    }

    /**
     * Get enabled payments based on selected method
     */
    protected function getEnabledPayments(string $method): array
    {
        $paymentMethods = [
            'bank_transfer' => ['bca_va', 'bni_va', 'bri_va', 'permata_va'],
            'e_wallet' => ['gopay', 'shopeepay', 'ovo', 'dana'],
            'credit_card' => ['credit_card'],
            'all' => ['credit_card', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'gopay', 'shopeepay', 'ovo', 'dana'],
        ];

        return $paymentMethods[$method] ?? $paymentMethods['all'];
    }

    /**
     * Handle payment callback from Midtrans
     */
    public function handlePaymentCallback(array $callbackData)
    {
        $this->verifyMidtransSignature($callbackData);

        $orderId = $callbackData['order_id'];
        $payment = $this->getPaymentByOrderId($orderId);

        if (!$payment) {
            throw new Exception("Payment not found for order: {$orderId}");
        }

        $status = $this->mapMidtransStatus($callbackData['transaction_status']);
        $paidAt = isset($callbackData['settlement_time']) ? $callbackData['settlement_time'] : null;

        $updatedPayment = $this->updatePaymentStatus($payment->payment_id, $status, $paidAt);

        if ($status === 'success') {
            $this->updateOrderStatus($orderId, 'paid');
        }

        return $updatedPayment;
    }

    /**
     * Verify signature from Midtrans
     */
    protected function verifyMidtransSignature(array $data)
    {
        $signature = hash('sha512',
            $data['order_id'] .
            $data['status_code'] .
            $data['gross_amount'] .
            Config::$serverKey
        );

        if ($signature !== $data['signature_key']) {
            throw new Exception('Invalid Midtrans signature');
        }
    }

    /**
     * Map Midtrans status to internal status
     */
    protected function mapMidtransStatus(string $midtransStatus): string
    {
        $statusMap = [
            'capture' => 'success',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'failure' => 'failed',
        ];

        return $statusMap[$midtransStatus] ?? 'failed';
    }

    /**
     * Get payment by ID
     */
    public function getPayment(string $paymentId)
    {
        return $this->paymentRepository->findById($paymentId);
    }

    /**
     * Get payment by order ID
     */
    public function getPaymentByOrderId(string $orderId)
    {
        return $this->paymentRepository->findByOrderId($orderId);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $paymentId, string $status, ?string $paidAt = null)
    {
        return $this->paymentRepository->updateStatus($paymentId, $status, $paidAt);
    }

    /**
     * Validate create payment data
     */
    protected function validateCreatePaymentData(array $data)
    {
        $validator = Validator::make($data, [
            'order_id' => 'required|string|exists:orders,order_id',
            'method' => 'required|string|in:bank_transfer,e_wallet,credit_card,all',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . $validator->errors()->first());
        }
    }

    /**
     * Get order details
     */
    protected function getOrderDetails(string $orderId)
    {
        try {
            return app(\App\Interfaces\Repositories\OrderRepositoryInterface::class)->findWithDetails($orderId);
        } catch (Exception $e) {
            throw new Exception("Order not found: {$orderId}");
        }
    }

    /**
     * Update order status after payment success
     */
    protected function updateOrderStatus(string $orderId, string $status)
    {
        try {
            return app(\App\Interfaces\Repositories\OrderRepositoryInterface::class)->updateStatus($orderId, $status);
        } catch (Exception $e) {
            // Log error but don't throw - payment success is more important
            logger()->error("Failed to update order status: {$e->getMessage()}");
        }
    }
}