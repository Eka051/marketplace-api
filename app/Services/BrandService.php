<?php

namespace App\Services;

use App\Interfaces\Repositories\BrandRepositoryInterface;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

class BrandService
{
    protected $brandRepo;

    public function __construct(BrandRepositoryInterface $brandRepo)
    {
        $this->brandRepo = $brandRepo;
    }

    public function create(array $data)
    {
        $data['brand_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']);

        return $this->brandRepo->create($data);
    }

    public function createIfNotExists(array $data)
    {
        $existing = $this->brandRepo->findByName($data['name']);
        if ($existing) {
            return $existing;
        }

        return $this->create($data);
    }
}