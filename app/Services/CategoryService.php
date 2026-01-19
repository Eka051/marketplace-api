<?php

namespace App\Services;

use App\Repositories\Eloquent\CategoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

class CategoryService
{
    protected $categoryRepo;

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    private function validateCategoryData(array $data)
    {
        $rules = [
            'parent_id' => 'nullable|string|exists:categories,category_id',
            'name' => 'required|string|min:3|max:255'
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function createCategory(array $data)
    {
        $this->validateCategoryData($data);

        $existingCategory = $this->categoryRepo->findByName($data['name']);
        if ($existingCategory) {
            throw new InvalidArgumentException('Category with name ' . $data['name'] . ' already exists!');
        }

        $data['category_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']);

        return $this->categoryRepo->create($data);
    }

    public function addCategories(array $categories)
    {
        foreach ($categories as $data) {
            $this->validateCategoryData($data);
        }

        $preparedData = collect($categories)->map(function ($category) {
            return [
                'category_id' => (string) Ulid::generate(),
                'parent_id' => $category['parent_id'] ?? null,
                'name' => $category['name'],
                'slug' => Str::slug($category['name']) . '-' . rand(100, 999),
            ];
        })->toArray();

        return DB::transaction(function () use ($preparedData) {
            return $this->categoryRepo->bulkCreate($preparedData);
        });
    }

    public function getCategories(int $perPage) {
        return $this->categoryRepo->getAll($perPage);
    }

    public function getCategoryById(string $id) {
        return $this->categoryRepo->getById($id);
    }

    public function updateCategory(string $id, array $data)
    {
        $this->validateCategoryData($data);

        if (isset($data['name'])) {
            $existingCategory = $this->categoryRepo->findByName($data['name']);
            if ($existingCategory && $existingCategory->category_id !== $id) {
                throw new InvalidArgumentException('Category with name ' . $data['name'] . ' already exists!');
            }
            $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        }

        return $this->categoryRepo->update($id, $data);
    }

    public function deleteCategory(string $id) {
        try {
            return $this->categoryRepo->delete($id);
        } catch (ModelNotFoundException) {
            throw new InvalidArgumentException('Category not found');
        }
    }
}
