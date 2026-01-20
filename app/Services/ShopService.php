<?php

namespace App\Services;

use App\Models\Shop;
use App\Repositories\Eloquent\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use ImageKit\ImageKit;

class ShopService
{
    protected $shopRepository;
    protected $imageKit;

    public function __construct(ShopRepository $shopRepository)
    {
        $this->shopRepository = $shopRepository;
        $this->imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint'),
        );
    }

    public function searchShops(string $query, int $perPage = 10)
    {
        if (empty($query)) {
            throw new InvalidArgumentException('Query cannot be empty', 400);
        }

        return $this->shopRepository->searchShops($query, $perPage);
    }

    public function getShops(int $perPage = 10)
    {
        return Shop::with('owner')->paginate($perPage);
    }

    public function getDetailShop(string $id)
    {
        return $this->shopRepository->getById($id);
    }

    public function createShop(array $data, string $userId)
    {
        $existingShop = Shop::where('user_id', $userId)->first();
        if ($existingShop) {
            throw new InvalidArgumentException('User already has a shop', 400);
        }

        $this->validateShopData($data);

        $data['user_id'] = $userId;
        $data['shop_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);

        return $this->shopRepository->createShop($data);
    }

    public function updateShop(string $id, array $data)
    {
        $this->validateShopData($data);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        }

        return $this->shopRepository->updateShop($id, $data);
    }

    public function deleteShop(string $id)
    {
        try {
            return $this->shopRepository->deleteShop($id);
        } catch (ModelNotFoundException) {
            throw new InvalidArgumentException('Shop not found');
        }
    }

    public function uploadShopLogo($file, string $shopId)
    {
        if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/webp', 'image/png']) || $file->getSize() > 2048000) {
            throw new InvalidArgumentException('Invalid image file or file size exceeds 2MB', 400);
        }

        $uploadRes = $this->imageKit->upload([
            'file' => fopen($file->getRealPath(), 'r'),
            'fileName' => 'logo-' . $shopId . '-' . time() . '.' . $file->getClientOriginalExtension(),
            'folder' => '/shops/logos/'
        ]);

        $this->shopRepository->updateShop($shopId, [
            'logo_url' => $uploadRes->result->url
        ]);

        return $uploadRes->result->url;
    }

    public function uploadShopBanner($file, string $shopId)
    {
        if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/webp', 'image/png']) || $file->getSize() > 2048000) {
            throw new InvalidArgumentException('Invalid image file or file size exceeds 2MB', 400);
        }

        $uploadRes = $this->imageKit->upload([
            'file' => fopen($file->getRealPath(), 'r'),
            'fileName' => 'banner-' . $shopId . '-' . time() . '.' . $file->getClientOriginalExtension(),
            'folder' => '/shops/banners/'
        ]);

        $this->shopRepository->updateShop($shopId, [
            'banner_url' => $uploadRes->result->url
        ]);

        return $uploadRes->result->url;
    }

    private function validateShopData(array $data)
    {
        $rules = [
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}