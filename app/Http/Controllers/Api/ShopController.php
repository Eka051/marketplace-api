<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShopService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

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

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage()
        ], 200);
    }

    public function store(Request $request)
    {
        try {
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

            $shop->refresh();

            return response()->json([
                'success' => true,
                'data' => $shop
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $shop = $this->shopService->getDetailShop($id);
            return response()->json([
                'success' => true,
                'data' => $shop
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
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

            return response()->json([
                'success' => true,
                'data' => $shop
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shop: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->shopService->deleteShop($id);
            return response()->json([
                'success' => true,
                'message' => 'Shop deleted successfully'
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shop: ' . $e->getMessage(),
            ], 500);
        }
    }
}
