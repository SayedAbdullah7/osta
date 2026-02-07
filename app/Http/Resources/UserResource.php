<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'phone' => $this->phone,
            'email' => $this->email,
//            'account' => $this->account,
            'gender' => $this->gender?'male':'female',
            'date_of_birth' => $this->date_of_birth,
            'personal_media_url' => $this->getFirstMediaUrl('personal'),
            'token' => $this->token,
            'country_id' => $this->country_id,
            'country' => new CountryResource($this->whenLoaded('country')), // Conditionally include 'country' if it's loaded
            'total_completed_orders' => $this->whenCounted('orders_count'),
            'review_statistics' => new ProviderReviewStatisticsResource($this->whenLoaded('reviewStatistics')),
            'is_notification_enabled' => (bool) $this->deviceTokens()->where('is_set_notification', true)->exists(),
        ];
    }
}
