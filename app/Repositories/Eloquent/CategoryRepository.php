<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\CategoryRepositoryInterface;
use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface {
    public function create(array $data)
    {
        return Category::create($data);
    }

    public function bulkCreate(array $categories)
    {
        return Category::insert($categories);
    }

    public function findByNameAndShop(string $name, string $shopId)
    {
        return Category::where('name', $name)->where('shop_id', $shopId)->first();
    }

    public function getAll()
    {
        return Category::with('products', 'parent', 'children', 'shop')->get();
    }

    public function getById(string $id)
    {
        return Category::with('products', 'parent', 'children', 'shop')->findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function delete(string $id)
    {
        $category = Category::findOrFail($id);
        return $category->delete();
    }
}