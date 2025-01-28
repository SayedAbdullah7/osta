<?php

namespace App\Http\Resources;

use App\Models\User;
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
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'order_id' => $this->order_id,
            'reviewer' => $this->whenLoaded('reviewable', function () {
                if($this->reviewable_type == User::class) {
                    return new UserResource($this->reviewable);
                }else{
                    return new ProviderResource($this->reviewable);
                }
            }),
            'reviewed' => $this->whenLoaded('reviewed', function () {
                if($this->reviewed_type == User::class) {
                    return new UserResource($this->reviewed);
                }else{
                    return new ProviderResource($this->reviewed);
                }
            }),
//            'reviewer' => [
//                'id' => $this->reviewable->id,
//                'type' => class_basename($this->reviewable),
//                'name' => $this->reviewable->name ?? $this->reviewable->username,
//            ],
//            'reviewed' => [
//                'id' => $this->reviewed->id,
//                'type' => class_basename($this->reviewed),
//                'name' => $this->reviewed->name ?? $this->reviewed->username,
//            ],
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
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
