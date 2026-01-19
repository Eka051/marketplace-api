<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\BrandRepositoryInterface;
use App\Models\Brand;

class BrandRepository implements BrandRepositoryInterface {
    public function create(array $data)
    {
        return Brand::create($data);
    }

    public function bulkCreate(array $brands)
    {
        return Brand::insert($brands);
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

    public function getByIds(array $brandIds)
    {
        return Brand::where('brand_id', $brandIds)->with([
            'products'
        ])->get();
    }

    public function update(string $id, array $data)
    {
        $brand = Brand::findOrFail($id);
        $brand->update($data);
        return $brand;
    }

    public function delete(string $id)
    {
        $brand = Brand::findOrFail($id);
        return $brand->delete();
    }
}