<?php

namespace App\Services;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use ImageKit\ImageKit;

class ProductService
{
    protected $productRepo;
    protected $imageKit;

    public function __construct(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
        $this->imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint'),
        );
    }

    public function validateProductData(array $data, bool $isCreate = true)
    {
        $rules = [
            'shop_id' => 'required|string',
            'brand_id' => 'nullable|string',
            'category_id' => 'nullable|string',
            'name' => 'required|string|min:5|max:255',
            'price' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ];

        if ($isCreate) {
            $rules['shop_id'] .= '|exists:shops,shop_id';
            $rules['category_id'] .= '|exists:categories,category_id';
        }

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function getProducts(int $perPage = 15)
    {
        return $this->productRepo->getAll($perPage);
    }

    public function getDetailProduct(string $productId)
    {
        return $this->productRepo->getById($productId);
    }

    public function searchProducts(string $query, int $perPage = 10)
    {
        if (empty($query)) {
            throw new InvalidArgumentException('Query cannot be empty');
        }

        $products = $this->productRepo->searchProducts($query, $perPage);

        return $products;
    }

    public function createProduct(array $data)
    {
        $this->validateProductData($data, false);

        $data['product_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        $data['is_active'] = true;

        return $this->productRepo->createProduct($data);
    }

    public function updateData(string $id, array $data)
    {
        $this->validateProductData($data, false);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        }

        return $this->productRepo->updateProduct($id, $data);
    }

    public function addMultipleProducts(array $products)
    {
        foreach ($products as $product) {
            $this->validateProductData($product);
        }

        $preparedData = collect($products)->map(function ($product) {
            return [
                'product_id' => (string) Ulid::generate(),
                'shop_id' => $product['shop_id'],
                'category_id' => $product['category_id'],
                'brand_id' => $product['brand_id'] ?? null,
                'name' => $product['name'],
                'slug' => Str::slug($product['name']) . '-' . rand(100, 999),
                'description' => $product['description'] ?? null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
        })->toArray();

        return DB::transaction(function () use ($preparedData) {
            return $this->productRepo->bulkCreate($preparedData);
        });
    }

    public function deleteMultipleProducts(array $productIds)
    {
        return $this->productRepo->bulkDelete($productIds);
    }

    public function uploadProductImages(array $files, string $productId)
    {
        foreach ($files as $file) {
            if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/webp', 'image/png'])) {
                throw new InvalidArgumentException('Invalid image file');
            }

            $uploadRes = $this->imageKit->upload([
                'file' => fopen($file->getRealPath(), 'r'),
                'fileName' => $file->getClientOriginalName(),
                'folder' => '/products/'
            ]);

            ProductImage::create([
                'product_id' => $productId,
                'image_path' => $uploadRes->result->url,
            ]);
        }
    }

    public function deleteProduct(string $id)
    {
        return $this->productRepo->deleteProduct($id);
    }
}
