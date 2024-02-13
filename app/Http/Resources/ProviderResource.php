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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'is_phone_verified' => (bool)$this->is_phone_verified,
//            'country_id' => $this->country_id,
//            'city_id' => $this->city_id,
            'country' => new CountryResource($this->country),
            'city' => new CityResource($this->city),
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
        ];
    }
}
