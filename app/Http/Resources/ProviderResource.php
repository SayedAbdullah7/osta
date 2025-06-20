<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
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
//            'first_name' => $this->first_name,
//            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_phone_verified' => (bool)$this->is_phone_verified,
            'is_approved' => (bool)$this->is_approved,
            'is_new' => (bool)$this->is_new,

//            'country_id' => $this->country_id,
//            'city_id' => $this->city_id,
            'country' => new CountryResource($this->country),
            'city' => new CityResource($this->city),
            'gender' => $this->gender?'male':'female',
            'services' => ServiceResource::collection($this->services),
            'bank_account' => new BankAccountResource($this->bank_account),
//            'remember_token' => $this->remember_token,
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
            'personal_media_url' => $this->getFirstMediaUrl('personal'),
            'front_id_media_url' => $this->getFirstMediaUrl('front_id'),
            'back_id_media_url' => $this->getFirstMediaUrl('back_id'),
            'certificate_media_url' => $this->getFirstMediaUrl('certificate'),
            'token' => $this->token,
            'total_completed_orders' => $this->whenCounted('orders_count'),
            'review_statistics' => new ProviderReviewStatisticsResource($this->whenLoaded('reviewStatistics')),
            'reviews_received' =>  ReviewResource::collection($this->whenLoaded('reviewsReceived')),
        ];
    }
}
