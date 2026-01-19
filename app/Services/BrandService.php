<?php

namespace App\Services;

use App\Repositories\Eloquent\BrandRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Uid\Ulid;

class BrandService
{
    protected $brandRepo;

    public function __construct(BrandRepository $brandRepo)
    {
        $this->brandRepo = $brandRepo;
    }

    private function validateBrand(array $data)
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'logo_url' => 'nullable|string'
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function create(array $data)
    {
        $data['brand_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']);

        return $this->brandRepo->create($data);
    }

    public function addBrands(array $brands)
    {
        foreach ($brands as $brand) {
            $this->validateBrand($brand);
        }

        $preparedData = collect($brands)->map(function ($brand) {
            return [
                'brand_id' => (string) Ulid::generate(),
                'name' => $brand['name'],
                'slug' => Str::slug($brand['name']) . '-' . rand(100, 999),
            ];
        })->toArray();

        return DB::transaction(function () use ($preparedData) {
            $this->brandRepo->bulkCreate($preparedData);
            $brandId = collect($preparedData)->pluck('brand_id')->toArray();
            return $this->brandRepo->getByIds($brandId);
        });
    }

    public function getBrands(int $perPage)
    {
        return $this->brandRepo->getAll($perPage);
    }
}
