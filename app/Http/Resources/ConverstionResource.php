<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConverstionResource extends JsonResource
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
            'type' => $this->type,
            'is_active' => $this->is_active,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
//            'last_message_at' => new MessageResource($this->lastMessage),
            'last_message' => $this->whenLoaded('lastMessage', function () {
                return new MessageResource($this->lastMessage);
            }),

            'user' => $this->whenLoaded('users', function () {
                return new UserResource($this->users->first());
                return $this->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'personal_media_url' => $user->getFirstMediaUrl('personal'),
                    ];
                });
            }),
            'provider' => $this->whenLoaded('providers', function () {
                return new ProviderResource($this->providers->first());
                return $this->providers->map(function ($provider) {
                    return [
                        'id' => $provider->id,
                        'name' => $provider->name,
                        'phone' => $provider->phone,
                        'personal_media_url' => $provider->getFirstMediaUrl('personal'),
                    ];
                });
            }),
            'order' => $this->whenLoaded('model', function () {
                if($this->model_type == Order::class){
                    return new OrderResource($this->model);
                }
            }),
                // Add any additional fields or relationships you want to include
        ];
    }
}
