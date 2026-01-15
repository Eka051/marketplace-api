<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\BrandRepositoryInterface;
use App\Models\Brand;

class BrandRepository implements BrandRepositoryInterface {
    public function create(array $data)
    {
        return Brand::create($data);
    }

    public function bulkCreate(array $categories)
    {
        return Brand::insert($categories);
    }

    public function findByName(string $name)
    {
        return Brand::where('name', $name)->first();
    }

    public function getAll()
    {
        return Brand::with('products')->get();
    }

    public function getById(string $id)
    {
        return Brand::with('products')->findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $Brand = Brand::findOrFail($id);
        $Brand->update($data);
        return $Brand;
    }

    public function delete(string $id)
    {
        $Brand = Brand::findOrFail($id);
        return $Brand->delete();
    }
}