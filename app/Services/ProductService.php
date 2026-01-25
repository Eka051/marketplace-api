<?php

namespace App\Services;

use App\Interfaces\Repositories\ProductRepositoryInterface;
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

        $currentPosition = $this->productRepo->getProductImagesCount($productId);

        foreach ($images as $image) {
            if (is_string($image)) {
                if (!filter_var($image, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('Invalid image URL');
                }
                $this->productRepo->createProductImage([
                    'product_id' => $productId,
                    'image_path' => $image,
                    'position' => $currentPosition++,
                ]);
            } elseif ($image instanceof UploadedFile) {
                if (!$image->isValid() || !in_array($image->getMimeType(), ['image/jpeg', 'image/webp', 'image/png', 'image/gif'])) {
                    throw new InvalidArgumentException('Invalid image file');
                }
                
                $uploadRes = $this->imageKit->upload([
                    'file' => fopen($image->getRealPath(), 'r'),
                    'fileName' => $image->getClientOriginalName(),
                    'folder' => '/shops/products/'
                ]);
                
                $this->productRepo->createProductImage([
                    'product_id' => $productId,
                    'image_path' => $uploadRes->result->url,
                    'position' => $currentPosition++,
                ]);
            } else {
                throw new InvalidArgumentException('Image must be a file or URL string');
            }
        }
    }

    public function saveProductImageUrls(array $imageUrls, string $productId) {
        $currentPosition = $this->productRepo->getProductImagesCount($productId);
        
        foreach ($imageUrls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid image URL: ' . $url);
            }
            $this->productRepo->createProductImage([
                'product_id' => $productId,
                'image_path' => $url,
                'position' => $currentPosition++,
            ]);
        }
    }

    public function deleteProduct(string $id)
    {
        return $this->productRepo->deleteProduct($id);
    }

    /**
     * Add multiple products with image support (files or URLs)
     * Handles both UploadedFile instances and URL strings
     * 
     * @param array $products Array of product data
     * @param array|null $imageFiles Array of image files grouped by product index (optional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function addMultipleProductsWithImages(array $products, ?array $imageFiles = null)
    {
        foreach ($products as &$product) {
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

        return DB::transaction(function () use ($preparedData, $imageFiles) {
            $this->productRepo->bulkCreate($preparedData);
            
            $productIds = collect($preparedData)->pluck('product_id')->toArray();
            $createdProducts = $this->productRepo->getBulkByIds($productIds);

            // Handle images if provided
            if ($imageFiles && is_array($imageFiles)) {
                foreach ($imageFiles as $index => $images) {
                    if (isset($createdProducts[$index]) && !empty($images)) {
                        $productId = $createdProducts[$index]->product_id;
                        
                        $this->uploadProductImages($images, $productId);
                    }
                }
            }

            return $this->productRepo->getBulkByIds($productIds);
        });
    }

    private function validateProductData(array $data)
    {
        $rules = [
            'shop_id' => 'required|string|exists:shops,shop_id',
            'brand_id' => 'nullable|string|exists:brands,brand_id',
            'category_id' => 'nullable|string|exists:categories,category_id',
            'name' => 'required|string|min:5|max:255',
            'price' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
