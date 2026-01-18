<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $paginator = $this->categoryService->getCategories($perPage);

            return response()->json([
                'success' => true,
                'data' => CategoryResource::collection($paginator->items()),
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

    public function show(string $categoryId)
    {
        try {
            $category = $this->categoryService->getCategoryById($categoryId);

            return response()->json([
                'success' => true,
                'data' => $category
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
            $category = $this->categoryService->createCategory($data);

            $category->refresh();
            return response()->json([
                'success' => true,
                'data' => new CategoryResource($category)
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
                'message' => 'Failed to create category::->' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkStore(Request $request)
    {
        try {
            $data = $request->json()->all();
            $categories = $data['categories'] ?? null;

            if (!$categories || !is_array($categories) || empty($categories)) {
                throw new InvalidArgumentException('Category array is required');
            }

            $createdCategories = $this->categoryService->addCategories($categories);

            return response()->json([
                'success' => true,
                'data' => CategoryResource::collection($createdCategories)
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
                'message' => 'Failed to create category::->' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $categoryId) {
        try {
            $data = $request->json()->all();
            $category = $this->categoryService->updateCategory($categoryId, $data);

            $category->refresh();
            
            return response()->json([
                'success' => true,
                'data' => new CategoryResource($category)
            ]);
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
                'message' => 'Failed to create category::->' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $categoryId) {
        try {
            $this->categoryService->deleteCategory($categoryId);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
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
                'message' => 'Failed to create category::->' . $e->getMessage(),
            ], 500);
        }
    }
}
