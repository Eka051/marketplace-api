<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ShopResource;

class ProductResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product_id,
            'name' => $this->name,
            'price' => $this->price,
            'stock' => $this->stock,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'category_id' => $this->category->category_id,
                    'name' => $this->category->name,
                ];
            }),
            'skus' => $this->whenLoaded('skus', function () {
                return $this->skus->map(function ($sku) {
                    return [
                        'sku_id' => $sku->sku_id,
                        'price' => $sku->price,
                        'stock' => $sku->stock,
                        'attribute_options' => $sku->whenLoaded('attributeOptions', function () use ($sku) {
                            return $sku->attributeOptions->map(function ($option) {
                                return [
                                    'option_id' => $option->option_id,
                                    'name' => $option->name,
                                    'value' => $option->value,
                                ];
                            });
                        })
                    ];
                });
            }),
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'attributes' => $this->whenLoaded('attributes', function () {
                return $this->attributes->map(function ($attr) {
                    return [
                        'attribute_id' => $attr->attribute_id,
                        'name' => $attr->name,
                        'value' => $attr->value,
                    ];
                });
            }),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->sortBy('position')->map(function ($image) {
                    return [
                        'image_path' => $image->image_path,
                        'position' => $image->position,
                    ];
                })->values();
            }),
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'review_id' => $review->review_id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'user' => $review->user->name,
                    ];
                });
            }),
        ];
    }
}
