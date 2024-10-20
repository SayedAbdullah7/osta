<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpaceResource extends JsonResource
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
//            'sub_service_id' => $this->whenLoaded('pivot', $this->pivot->sub_service_id),
//            'space_id' => $this->whenLoaded('pivot', $this->pivot->space_id),
            'max_price' => $this->whenLoaded('pivot', $this->pivot->max_price),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
//            'pivot' => [
//                'sub_service_id' => $this->pivot->sub_service_id,
//                'space_id' => $this->pivot->space_id,
//                'max_price' => $this->pivot->max_price,
//            ],
        ];
    }
}
