<?php

namespace App\Services;

use App\Models\Brand;
use App\Repositories\Eloquent\BrandRepository;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

class BrandService
{
    protected $brandRepo;

    public function __construct(BrandRepository $brandRepo)
    {
        $this->brandRepo = $brandRepo;
    }

    public function create(array $data)
    {
        $data['brand_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']);

        return $this->brandRepo->create($data);
    }

    public function createIfNotExists(array $data): Brand
    {
        $existing = $this->brandRepo->findByName($data['name']);
        if ($existing) {
            return $existing;
        }

        return $this->create($data);
    }
}