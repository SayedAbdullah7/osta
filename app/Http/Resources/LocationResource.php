<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'street' => $this->street,
            'apartment_number' => $this->apartment_number,
            'floor_number' => $this->floor_number,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'desc' => $this->desc,
            'is_default' => $this->is_default,
            'city' => new CityResource($this->whenLoaded('city')),
            'user' => new UserResource($this->whenLoaded('user')),
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
        ];
    }
}
