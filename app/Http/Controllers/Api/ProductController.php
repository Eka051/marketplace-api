<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        $perPage = $request->get('per_page', 15);
        $paginator = $this->productService->getProducts($perPage);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'last_page' => $paginator->lastPage()
        ], 200);
    }

    public function show(string $productId)
    {
        $product = $this->productService->getDetailProduct($productId);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product)
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
                'data' => ProductResource::collection($paginator->items()),
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

            // Handle images (both file upload and URL array)
            if ($request->has('images')) {
                $images = $request->input('images');
                if ($images && is_array($images)) {
                    if ($request->hasFile('images')) {
                        $this->productService->uploadProductImages($request->file('images'), $product->product_id);
                    } else {
                        $this->productService->saveProductImageUrls($images, $product->product_id);
                    }
                }
            }

            $product->refresh();
            $product->load([
                'category',
                'skus.attributeOptions',
                'brand',
                'attributes',
                'images',
            ]);

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product)
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkStore(Request $request)
    {
        try {
            $rawBody = $request->getContent();

            $decoded = json_decode($rawBody, true);

            $data = $decoded['products'] ?? null;

            if (!$data || !is_array($data) || empty($data)) {
                throw new InvalidArgumentException('Product array is required');
            }

            $createdProducts = $this->productService->addMultipleProducts($data);

            foreach ($createdProducts as $index => $product) {
                $originalProduct = $data[$index];
                if (isset($originalProduct['images']) && is_array($originalProduct['images'])) {
                    $this->productService->saveProductImageUrls($originalProduct['images'], $product->product_id);
                    $product->refresh();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Products created successfully',
                'data' => $createdProducts,
                'count' => count($createdProducts)
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create products: ' . $e->getMessage(),
            ], 500);
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

            $product->refresh();

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product)
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
            ], 500);
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
            ], $e->getCode() ?: 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage(),
            ], 500);
        }
    }
}
