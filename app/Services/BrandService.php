<?php

namespace App\Services;

use App\Models\Brand;
use App\Repositories\Eloquent\BrandRepository;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ImageKit\ImageKit;
use InvalidArgumentException;
use Symfony\Component\Uid\Ulid;

class BrandService
{
    protected $brandRepo;
    protected $imageKit;

    public function __construct(BrandRepository $brandRepo)
    {
        $this->brandRepo = $brandRepo;
        $this->imageKit = new ImageKit(
            config('services.imagekit.public_key'),
            config('services.imagekit.private_key'),
            config('services.imagekit.url_endpoint'),
        );
    }

    public function create(array $data)
    {
        $this->validateBrandData($data);

        unset($data['logo_url']);
        $data['brand_id'] = (string) Ulid::generate();
        $data['slug'] = Str::slug($data['name']);

        return $this->brandRepo->create($data);
    }

    public function addBrands(array $brands)
    {
        foreach ($brands as $brand) {
            $this->validateBrandData($brand);
        }

        $preparedData = collect($brands)->map(function ($brand) {
            return [
                'brand_id' => (string) Ulid::generate(),
                'name' => $brand['name'],
                'slug' => Str::slug($brand['name']) . '-' . rand(100, 999),
                'logo_url' => $brand['logo_url'] ?? null,
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

    public function getDetailBrand(string $brandId)
    {
        return $this->brandRepo->getById($brandId);
    }

    public function update(string $brandId, array $data)
    {
        $this->validateBrandData($data);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . rand(100, 999);
        }

        return $this->brandRepo->update($brandId, $data);
    }

    public function delete(string $brandId)
    {
        $this->brandRepo->delete($brandId);
    }

    public function uploadBrandImage(string $brandId, $image)
    {
        try {
            $brand = $this->brandRepo->getById($brandId);
            if (!$brand) {
                throw new InvalidArgumentException('Brand not found');
            }

            $logoUrl = null;
            if (is_string($image)) {
                if (!filter_var($image, FILTER_VALIDATE_URL)) {
                    throw new InvalidArgumentException('Invalid image URL');
                }
                $logoUrl = $image;
            } elseif ($image instanceof UploadedFile) {
                if (!$image->isValid() || !in_array($image->getMimeType(), ['image/jpeg', 'image/webp', 'image/png'])) {
                    throw new InvalidArgumentException('Invalid image file');
                }

                $uploadRes = $this->imageKit->upload([
                    'file' => fopen($image->getRealPath(), 'r'),
                    'fileName' => $image->getClientOriginalName(),
                    'folder' => '/brands/logos/'
                ]);

                $logoUrl = $uploadRes->result->url;
            } else {
                throw new InvalidArgumentException('Image must be a file or URL string');
            }

            return $this->brandRepo->update($brandId, ['logo_url' => $logoUrl]);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new InvalidArgumentException('Failed to upload brand image::->' . $e->getMessage());
        }
    }

    public function saveBrandImageUrls(array $imageUrls, string $brandId)
    {
        foreach ($imageUrls as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid image URL: ' . $url);
            }
            $this->brandRepo->update($brandId, ['logo_url' => $url]);
        }
    }

    private function validateBrandData(array $data)
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
}
