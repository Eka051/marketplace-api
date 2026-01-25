<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
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
        $perPage = $request->get('per_page', 15);
        $paginator = $this->productService->getProducts($perPage);

        return $this->paginationResponse(
            $paginator,
            ProductResource::class,
            'Products retrieved successfully'
        );
    }

    public function show(string $productId)
    {
        $product = $this->productService->getDetailProduct($productId);

        return $this->successResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $perPage = $request->get('per_page', 10);
        $paginator = $this->productService->searchProducts($query, $perPage);


        return $this->paginationResponse(
            $paginator,
            ProductResource::class,
            'Products retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $product = $this->productService->createProduct($data);

        if ($request->hasFile('images')) {
            $this->productService->uploadProductImages($request->file('images'), $product->product_id);
        } elseif ($request->has('images')) {
            $images = $request->input('images');
            if (is_array($images)) {
                $this->productService->saveProductImageUrls($images, $product->product_id);
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

        return $this->successResponse(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    public function bulkStore(Request $request)
    {
        $products = null;
        $imageFiles = [];

        if ($request->isJson()) {
            $data = $request->json()->all();
            $products = $data['products'] ?? null;

            if ($products && is_array($products)) {
                foreach ($products as $index => $product) {
                    if (isset($product['images']) && is_array($product['images'])) {
                        $imageFiles[$index] = $product['images'];
                    }
                }
            }
        } else {
            $productsInput = $request->input('products');

            if (is_string($productsInput)) {
                $products = json_decode($productsInput, true);
            } else {
                $products = $productsInput;
            }

            $allFiles = $request->allFiles();

            if ($request->hasFile('images')) {
                $images = $request->file('images');
                $imageFiles[0] = is_array($images) ? $images : [$images];
            }

            foreach ($allFiles as $key => $files) {
                if (preg_match('/^images\[(\d+)\]$/', $key, $matches)) {
                    $index = (int) $matches[1];
                    $imageFiles[$index] = is_array($files) ? $files : [$files];
                } elseif (preg_match('/^images_(\d+)$/', $key, $matches)) {
                    $index = (int) $matches[1];
                    $imageFiles[$index] = is_array($files) ? $files : [$files];
                }
            }
        }

        if (!$products || !is_array($products) || empty($products)) {
            throw new InvalidArgumentException('Product array is required');
        }
        $createdProducts = $this->productService->addMultipleProductsWithImages(
            $products,
            !empty($imageFiles) ? $imageFiles : null
        );

        return $this->successResponse(
            [
                'items' => ProductResource::collection($createdProducts),
                'count' => count($createdProducts)
            ],
            'Products created successfully',
            201
        );
    }

    public function update(Request $request, string $id)
    {
        $data = $request->all();
        $product = $this->productService->updateData($id, $data);

        if ($request->hasFile('images')) {
            $this->productService->uploadProductImages($request->file('images'), $id);
        }

        $product->refresh();

        return $this->successResponse(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    public function destroy(string $id)
    {
        $this->productService->deleteProduct($id);
        return $this->successResponse(
            null,
            'Product deleted successfully',
            200
        );
    }
}
