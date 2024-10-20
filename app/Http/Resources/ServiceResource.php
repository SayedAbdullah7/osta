<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
//            'min_price' => $this->min_price,
//            'max_price' => $this->max_price,
            'category' => $this->category,
//            'image_url' => $this->when($this->hasMedia('images'), $this->getFirstMediaUrl('images')),
        ];

    }
}
