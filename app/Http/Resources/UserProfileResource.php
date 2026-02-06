<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'profile_picture' => $this->profile_picture,
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
