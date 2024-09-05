<?php

namespace App\Http\Resources;

use App\Models\Service;
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
            'unknown_problem'=>$this->unknown_problem,
            'max_allowed_price' => $this->max_allowed_price,
            'location_latitude' => $this->location_latitude,
            'location_longitude' => $this->location_longitude,
            'location_desc' => $this->location_desc,
            'user' => new UserResource($this->whenLoaded('user')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
//            'location' => new LocationResource($this->whenLoaded('location')),
            'sub_services' => SubServiceResource::collection($this->whenLoaded('subServices')),
            'images' => $this->getMedia('images')->map(function (Media $media) {
                return $media->getFullUrl();
            }),
            'voice_desc' => $this->getFirstMediaUrl('voice_desc'),

            'total_pending_offers' => $this->whenCounted('offers_count'),
            'distance'=> $this->distance,
            'offers' => OfferResource::collection($this->whenLoaded('offers')),
            'is_there_more' => $this->when(
                $this->offers_count !== null,
                $this->offers_count > 2
            ),//            ' => $this->whenCounted('orders_count'),
//            'images2' => $this->getMediaUrls('images'),
        ];
    }
}
