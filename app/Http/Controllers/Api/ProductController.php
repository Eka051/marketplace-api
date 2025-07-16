<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/products",
     * operationId="getProductsList",
     * tags={"Products"},
     * summary="Get list of products",
     * description="Returns list of products",
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product"))
     * )
     * )
     * )
     */

    public function index()
    {
        $products = Product::all();
        return response()->json(['data' => $products]);
    }

    /**
     * @OA\Post(
     * path="/api/products",
     * operationId="storeProduct",
     * tags={"Products"},
     * summary="Store new product",
     * description="Returns product data",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/StoreProductRequest")
     * ),
     * @OA\Response(
     * response=201,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="data", ref="#/components/schemas/Product")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocessable Entity (Validation error)"
     * )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku',
            'image' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'stock' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $product = Product::create($request->all());
        return response()->json(['data' => $product], 201);
    }

    /**
     * @OA\Get(
     * path="/api/products/{product}",
     * operationId="getProductById",
     * tags={"Products"},
     * summary="Get product information",
     * description="Returns product data",
     * @OA\Parameter(
     * name="product",
     * description="Product id",
     * required=true,
     * in="path",
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="data", ref="#/components/schemas/Product")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Resource Not Found"
     * )
     * )
     */
    public function show(Product $product)
    {
        return response()->json(['data' => $product]);
    }

   /**
     * @OA\Put(
     * path="/api/products/{product}",
     * operationId="updateProduct",
     * tags={"Products"},
     * summary="Update existing product",
     * description="Returns updated product data",
     * @OA\Parameter(
     * name="product",
     * description="Product id",
     * required=true,
     * in="path",
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/UpdateProductRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="data", ref="#/components/schemas/Product")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Resource Not Found"
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocessable Entity (Validation error)"
     * )
     * )
     */

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer|min:0',
            'sku' => 'sometimes|required|string|unique:products,sku,' . $product->id,
            'image' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'stock' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->all());
        return response()->json(['data' => $product]);
    }

    /**
     * @OA\Delete(
     * path="/api/products/{product}",
     * operationId="deleteProduct",
     * tags={"Products"},
     * summary="Delete existing product",
     * description="Deletes a record and returns no content",
     * @OA\Parameter(
     * name="product",
     * description="Product id",
     * required=true,
     * in="path",
     * @OA\Schema(
     * type="integer"
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Successful operation",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="Resource Not Found"
     * )
     * )
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 204);
    }
}
