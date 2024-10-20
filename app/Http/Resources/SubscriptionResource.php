<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (string) $this->price,  // Convert float to string
            'price_before_discount' => $this->price_before_discount ? (string) $this->price_before_discount : null,  // Convert float to string if not null
//            'discount_expiration_date' => $this->discount_expiration_date ? $this->discount_expiration_date->toDateString() : null,
//            'level_id' => $this->level_id,
            'fee_percentage' => (string) $this->fee_percentage,  // Convert float to string
            'number_of_days' => $this->number_of_days,
//            'is_available' => (bool) $this->is_available,
//            'created_at' => $this->created_at->toDateTimeString(),
//            'updated_at' => $this->updated_at->toDateTimeString(),
        ];    }

}
