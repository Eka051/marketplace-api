<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'min_purchase' => $this->min_purchase,
            'max_discount' => $this->max_discount,
            'quota' => $this->quota,
            'is_active' => $this->is_active,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
        ];
    }
}
