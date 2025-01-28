<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        $data= [
            'id' => $this->id,
            'name' => $this->name,
//            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
            'type' => $this->type,
            'service' => new ServiceResource($this->whenLoaded('service')),
            'spaces' => SpaceResource::collection($this->whenLoaded('spaces')),
            'image' => $this->getFirstMediaUrl('default', 'thumb'),
        ];
        if ($this->pivot) {
            $data += [
                'quantity' => $this->pivot->quantity,
            ];
        }
        return $data;
    }
}
