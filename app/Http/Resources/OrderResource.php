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
//            'warranty_ex' => $this->warranty,
            'status' => $this->status,
            'is_confirmed' => $this->is_confirmed,
            'desc' => $this->desc,
            'price' => $this->price,
            'unknown_problem'=>$this->unknown_problem,
            'max_allowed_price' => $this->max_allowed_price,
            'location_latitude' => $this->location_latitude,
            'location_longitude' => $this->location_longitude,
            'location_desc' => $this->location_desc,
            'location_name' => $this->location_name,
            'user' => new UserResource($this->whenLoaded('user')),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'provider' => new ProviderResource($this->whenLoaded('provider')),
//            'location' => new LocationResource($this->whenLoaded('location')),
//            'sub_services' => SubServiceResource::collection($this->whenLoaded('subServices')),
            'sub_services' => OrderSubServiceResource::collection($this->whenLoaded('orderSubServices')),
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
            'offers_count' => $this->offers_count,
            'warranty' => $this->whenLoaded('warranty', function () {
                $data['expiration_date'] = $this->created_at->copy()->addMonths($this->duration_months);
                return new WarrantyResource($this->warranty,$this->created_at);
            }),
            'provider_review' => new ReviewResource($this->whenLoaded('providerReview')),
            'user_review' => new ReviewResource($this->whenLoaded('userReview')),
            'created_at' => date_format($this->created_at, 'Y-m-d H:i:s'),

        ];
    }
}
