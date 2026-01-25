<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShopResource;
use App\Services\ShopService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $paginator = $this->shopService->getShops($perPage);

        return $this->paginationResponse(
            $paginator,
            ShopResource::class,
            'Shops retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $data = $request->only(['name', 'description']);
        $userId = $request->user()->user_id;

        $shop = $this->shopService->createShop($data, $userId);

        if ($request->hasFile('logo')) {
            $logoUrl = $this->shopService->uploadShopLogo($request->file('logo'), $shop->shop_id);
            $shop->logo_url = $logoUrl;
        }

        if ($request->hasFile('banner')) {
            $bannerUrl = $this->shopService->uploadShopBanner($request->file('banner'), $shop->shop_id);
            $shop->banner_url = $bannerUrl;
        }

        $shop->save();
        $shop->refresh();

        return $this->successResponse(
            new ShopResource($shop),
            'Shop created successfully',
            201
        );
    }

    public function show(string $id)
    {
        $shop = $this->shopService->getDetailShop($id);

        return $this->successResponse(
            $shop,
            'Shop retrieved successfully',
        );
    }

    public function update(Request $request, string $id)
    {
        $data = $request->only(['name', 'description']);

        $shop = $this->shopService->updateShop($id, $data);

        if ($request->hasFile('logo')) {
            $logoUrl = $this->shopService->uploadShopLogo($request->file('logo'), $shop->shop_id);
            $shop->logo_url = $logoUrl;
        }

        if ($request->hasFile('banner')) {
            $bannerUrl = $this->shopService->uploadShopBanner($request->file('banner'), $shop->shop_id);
            $shop->banner_url = $bannerUrl;
        }

        $shop->refresh();

        return $this->successResponse(
            $shop,
            'Shop updated successfully',
        );
    }

    public function destroy(string $id)
    {
        $this->shopService->deleteShop($id);

        return $this->successResponse(
            null,
            'Shop deleted successfully',
        );
    }
}
