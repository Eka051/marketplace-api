<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
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
        $perPage = $request->get('per_page', 10);
        $paginator = $this->categoryService->getCategories($perPage);

        return $this->paginationResponse(
            $paginator,
            CategoryResource::class,
            'Categories retrieved successfully'
        );
    }

    public function show(string $categoryId)
    {
        $category = $this->categoryService->getCategoryById($categoryId);

        return $this->successResponse(
            $category,
            'Category retrieved successfully',
        );
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $category = $this->categoryService->createCategory($data);

        $category->refresh();

        return $this->successResponse(
            $category,
            'Category created successfully',
            201
        );
    }

    public function bulkStore(Request $request)
    {
        $data = $request->json()->all();
        $categories = $data['categories'] ?? null;

        if (!$categories || !is_array($categories) || empty($categories)) {
            throw new InvalidArgumentException('Categories array is required');
        }

        $createdCategories = $this->categoryService->addCategories($categories);

        return $this->successResponse(
            CategoryResource::collection($createdCategories),
            'Categories created successfully',
            201
        );
    }

    public function update(Request $request, string $categoryId)
    {
        $data = $request->json()->all();
        $category = $this->categoryService->updateCategory($categoryId, $data);

        $category->refresh();

        return $this->successResponse(
            new CategoryResource($category),
            'Category updated successfully',
        );
    }

    public function destroy(string $categoryId)
    {
        $this->categoryService->deleteCategory($categoryId);

        return $this->successResponse(
            null,
            'Category deleted successfully',
        );
    }
}
