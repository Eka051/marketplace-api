<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $paginator = $this->productService->getProducts($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage()
        ], 200);
    }

    public function show(string $productId) {
        $product = $this->productService->getDetailProduct($productId);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $perPage = $request->get('per_page', 10);
            $paginator = $this->productService->searchProducts($query, $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginator->items(),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage()
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $product = $this->productService->createProduct($data);

            if ($request->hasFile('images')) {
                $this->productService->uploadProductImages($request->file('images'), $product->product_id);
            }
            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->all();
            $product = $this->productService->updateData($id, $data);

            if ($request->hasFile('images')) {
                $this->productService->uploadProductImages($request->file('images'), $id);
            }

            return response()->json([
                'success' => true,
                'data' => $product
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->productService->deleteProduct($id);
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
