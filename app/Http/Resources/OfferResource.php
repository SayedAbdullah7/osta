<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
//            'arrival_from' => $this->arrival_from,
//            'arrival_to' => $this->arrival_to,
            'arrival_time' => $this->arrival_time,
            'price' => $this->price,
            'status' => $this->status,
//            'is_second' => $this->is_second,
            'provider_id' => $this->provider_id,
            'order_id' => $this->order_id,
            'distance' => (string)round($this->distance,2),
            'created_at' => $this->created_at?date_format($this->created_at, 'Y-m-d H:i:s'):null,
            'updated_at' => $this->updated_at?date_format($this->updated_at, 'Y-m-d H:i:s'):null,
//            'deleted_at' => $this->deleted_at?date_format($this->deleted_at, 'Y-m-d H:i:s'):null,
        "deleted_at"=> $this->deleted_at,
            'provider' => ProviderResource::make($this->whenLoaded('provider')),
            'order' => OrderResource::make($this->whenLoaded('order')),
        ];
    }
}
