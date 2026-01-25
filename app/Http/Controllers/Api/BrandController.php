<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Services\BrandService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BrandController extends Controller
{
    protected $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $paginator = $this->brandService->getBrands($perPage);

        return $this->paginationResponse(
            $paginator,
            BrandResource::class,
            'Brands retrieved successfully'
        );
    }

    public function show(string $brandId)
    {
        $brand = $this->brandService->getDetailBrand($brandId);

        return $this->successResponse(
            $brand,
            'Brand retrieved successfully'
        );
    }

    public function bulkStore(Request $request)
    {
        $data = $request->json()->all();
        $brands = $data['brands'] ?? null;

        if (!$brands || !is_array($brands) || empty($brands)) {
            throw new InvalidArgumentException('Brands array is required');
        }

        $createdBrands = $this->brandService->addBrands($brands);

        return $this->successResponse(
            BrandResource::collection($createdBrands),
            'Brands created successfully',
            201
        );
    }

    public function update(Request $request, string $brandId)
    {
        $data = $request->json()->all();
        $brand = $this->brandService->update($brandId, $data);

        $brand->refresh();

        return $this->successResponse(
            new BrandResource($brand),
            'Brand updated successfully'
        );
    }

    public function destroy(string $brandId)
    {
        $this->brandService->delete($brandId);

        return $this->successResponse(
            null,
            'Brand deleted successfully',
        );
    }
}
