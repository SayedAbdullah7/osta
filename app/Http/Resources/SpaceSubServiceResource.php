<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpaceSubServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'space_id' => $this->space_id,
            'sub_service_id' => $this->sub_service_id,
            'max_price' => $this->max_price,
            'description' => $this->description,
//            'space' => new SpaceResource($this->whenLoaded('space')),
//            'sub_service' => new SubServiceResource($this->whenLoaded('subService')),
            'space_name' => $this->whenLoaded('space', function () {
                return $this->space->name;
            }),
        ];
    }
}
