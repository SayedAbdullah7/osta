<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')), // Assuming UserResource exists
            'order' => new OrderResource($this->whenLoaded('order')), // Assuming OrderResource exists
            'provider' => new ProviderResource($this->whenLoaded('provider')), // Assuming ProviderResource exists
            'order_id' => $this->order_id,
            'provider_id' => $this->provider_id,
            'user_id' => $this->user_id,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
