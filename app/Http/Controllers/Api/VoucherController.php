<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VoucherResource;
use App\Services\VoucherService;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    protected $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $voucher = $this->voucherService->getAvailableVouchers($perPage);

        return $this->paginationResponse(
            $voucher,
            VoucherResource::class,
            'Vouchers retrieved successfully'
        );
    }

    public function show(string $id)
    {
        $voucher = $this->voucherService->getVoucherById($id);
        return $this->successResponse($voucher, 'Voucher retrieved successfully');
    }

    public function validate(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'total_price' => 'required|numeric|min:0'
        ]);

        $userId = $request->user()->id();
        $discount = $this->voucherService->calculateDiscount(
            $request->code,
            $userId,
            $request->total_price
        );

        return $this->successResponse($discount, 'Voucher validated successfully');
    }

    public function apply(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|string',
            'order_id' => 'required|string',
            'discount_amount' => 'required|numeric'
        ]);

        $userId = $request->user()->id();
        $usage = $this->voucherService->applyVoucher(
            $request->voucher_id,
            $userId,
            $request->order_id,
            $request->discount_amount
        );

        return $this->successResponse($usage, 'Voucher applied successfully');
    }

    public function refund(string $orderId)
    {
        $this->voucherService->refundVoucher($orderId);
        return $this->successResponse(null, 'Voucher refunded successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:vouchers,code|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'quota' => 'required|integer|min:1',
            'start_at' => 'nullable|date',
            'end_at' => 'null|date|after:start_at',
            'is_active' => 'boolean'
        ]);

        $voucher = $this->voucherService->createVoucher($request->all());
        return $this->successResponse($voucher, 'Voucher created successfully');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:vouchers,code|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'quota' => 'required|integer|min:1',
            'start_at' => 'nullable|date',
            'end_at' => 'null|date|after:start_at',
            'is_active' => 'boolean'
        ]);

        $voucher = $this->voucherService->updateVoucher($id, $request->all());
        return $this->successResponse($voucher, 'Voucher updated successfully');
    }

    public function destroy(string $id)
    {
        $this->voucherService->deleteVoucher($id);
        return $this->successResponse(null, 'Voucher deleted successfully');
    }

    public function adminIndex(Request $request)
    {
        $filters = $request->only(['status', 'type', 'search']);
        $perPage = $request->get('per_page', 10);

        $vouchers = $this->voucherService->getAllVouchers($filters, $perPage);
        return $this->successResponse($vouchers, 'Vouchers retrieved successfully');
    }
}
