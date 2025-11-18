<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku',
            'image' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], status: 400);
        }
        $product = Product::create($request->all());
        return response()->json([
            'success' => true,
            'data' => $product->only(
                'product_id',
                'name',
                'description',
                'price',
                'sku',
                'stock'
            )
        ], status: 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data' => $product->load('category')
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer|min:0',
            'sku' => "sometimes|required|string|unique:products,sku,{$product->getKey()}",
            'image' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'stock' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], status: 400);
        }

        $product->update($request->all());
        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], status: 200);
    }
}
