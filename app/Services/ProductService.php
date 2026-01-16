<?php

namespace App\Services;

use App\Interfaces\Repositories\ProductRepositoryInterface;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
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
    protected $categoryService;
    protected $brandService;
    protected $imageKit;

    public function __construct(ProductRepositoryInterface $productRepo, CategoryService $categoryService, BrandService $brandService)
    {
        $this->productRepo = $productRepo;
        $this->categoryService = $categoryService;
        $this->brandService = $brandService;
        $this->imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint'),
        );
    }

    private function validateProductData(array $data)
    {
        $rules = [
            'shop_id' => 'required|string|exists:shops,shop_id',
            'brand_id' => 'nullable|string|exists:brands,brand_id',
            'category_id' => 'nullable|string|exists:categories,category_id',
            'name' => 'required|string|min:5|max:255',
            'price' => 'required|integer|min:1',
            'stock' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ];

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
        $this->validateProductData($data);

        // Handle category: if object, create; if id, use directly
        if (isset($data['category'])) {
            if (is_array($data['category']) && isset($data['category'][0]['name'])) {
                $categoryName = $data['category'][0]['name'];
                $category = $this->categoryService->createIfNotExists(['name' => $categoryName]);
                $data['category_id'] = $category->category_id;
            } elseif (is_string($data['category'])) {
                $category = $this->categoryService->createIfNotExists(['name' => $data['category']]);
                $data['category_id'] = $category->category_id;
            }
            unset($data['category']);
        }

        // Handle brand: if object, create; if id, use directly
        if (isset($data['brand'])) {
            if (is_array($data['brand']) && isset($data['brand'][0]['name'])) {
                $brandName = $data['brand'][0]['name'];
                $brand = $this->brandService->createIfNotExists(['name' => $brandName]);
                $data['brand_id'] = $brand->brand_id;
            } elseif (is_string($data['brand'])) {
                $brand = $this->brandService->createIfNotExists(['name' => $data['brand']]);
                $data['brand_id'] = $brand->brand_id;
            }
            unset($data['brand']);
        }

        unset($data['images']);
        $data['product_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        $data['is_active'] = true;

        $product = $this->productRepo->createProduct($data);
        return $product;
    }

    public function updateData(string $id, array $data)
    {
        $this->validateProductData($data);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        }

        return $this->productRepo->updateProduct($id, $data);
    }

    public function addMultipleProducts(array $products)
    {
        foreach ($products as &$product) {
            // Handle category: if object, create; if id, use directly
            if (isset($product['category'])) {
                if (is_array($product['category']) && isset($product['category'][0]['name'])) {
                    $categoryName = $product['category'][0]['name'];
                    $category = $this->categoryService->createIfNotExists(['name' => $categoryName]);
                    $product['category_id'] = $category->category_id;
                } elseif (is_string($product['category'])) {
                    $category = $this->categoryService->createIfNotExists(['name' => $product['category']]);
                    $product['category_id'] = $category->category_id;
                }
                unset($product['category']);
            }

            // Handle brand: if object, create; if id, use directly
            if (isset($product['brand'])) {
                if (is_array($product['brand']) && isset($product['brand'][0]['name'])) {
                    $brandName = $product['brand'][0]['name'];
                    $brand = $this->brandService->createIfNotExists(['name' => $brandName]);
                    $product['brand_id'] = $brand->brand_id;
                } elseif (is_string($product['brand'])) {
                    $brand = $this->brandService->createIfNotExists(['name' => $product['brand']]);
                    $product['brand_id'] = $brand->brand_id;
                }
                unset($product['brand']);
            }

            unset($product['images']);
            $this->validateProductData($product);
        }

        $preparedData = collect($products)->map(function ($product) {
            return [
                'product_id' => (string) Ulid::generate(),
                'shop_id' => $product['shop_id'],
                'brand_id' => $product['brand_id'] ?? null,
                'category_id' => $product['category_id'] ?? null,
                'name' => $product['name'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'description' => $product['description'] ?? null,
                'slug' => Str::slug($product['name']) . '-' . rand(100, 999),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        return DB::transaction(function () use ($preparedData) {
            $this->productRepo->bulkCreate($preparedData);
            $productIds = collect($preparedData)->pluck('product_id')->toArray();
            return $this->productRepo->getBulkByIds($productIds);
        });
    }

    public function deleteMultipleProducts(array $productIds)
    {
        try {
            return $this->productRepo->bulkDelete($productIds);
        } catch (ModelNotFoundException) {
            throw new InvalidArgumentException('Product not found');
        }
    }

    public function uploadProductImages($images, string $productId)
    {
        if (!is_array($images)) {
            $images = [$images];
        }

        foreach ($images as $image) {
            if (is_string($image)) {
                if (!filter_var($image, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('Invalid image URL');
                }
                ProductImage::create([
                    'product_id' => $productId,
                    'image_path' => $image,
                ]);
            } elseif ($image instanceof UploadedFile) {
                if (!$image->isValid() || !in_array($image->getMimeType(), ['image/jpeg', 'image/webp', 'image/png', 'image/gif'])) {
                    throw new InvalidArgumentException('Invalid image file');
                }
                $uploadRes = $this->imageKit->upload([
                    'file' => fopen($image->getRealPath(), 'r'),
                    'fileName' => $image->getClientOriginalName(),
                    'folder' => '/products/'
                ]);
                ProductImage::create([
                    'product_id' => $productId,
                    'image_path' => $uploadRes->result->url,
                ]);
            } else {
                throw new InvalidArgumentException('Image must be a file or URL string');
            }
        }
    }

    public function saveProductImageUrls(array $imageUrls, string $productId) {
        foreach ($imageUrls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid image URL: ' . $url);
            }
            ProductImage::create([
                'product_id' => $productId,
                'image_path' => $url
            ]);
        }
    }

    public function deleteProduct(string $id)
    {
        return $this->productRepo->deleteProduct($id);
    }
}
