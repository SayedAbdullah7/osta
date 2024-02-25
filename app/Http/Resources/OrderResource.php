<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class OrderResource extends JsonResource
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
            'start' => $this->start,
            'end' => $this->end,
            'warranty_id' => $this->warranty_id,
            'status' => $this->status,
            'desc' => $this->desc,
            'price' => $this->price,
            'user' => new UserResource($this->whenLoaded('user')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'sub_services' => SubServiceResource::collection($this->whenLoaded('subServices')),
            'images' => $this->getMedia('images')->map(function (Media $media) {
                return $media->getFullUrl();
            }),
//            'images2' => $this->getMediaUrls('images'),
        ];
    }
}
