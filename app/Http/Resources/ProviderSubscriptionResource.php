<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderSubscriptionResource extends JsonResource
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
            'provider_id' => $this->provider_id,
            'subscription_id' => $this->subscription_id,
            'subscription' => $this->whenLoaded('subscription', function () {
                return new SubscriptionResource($this->subscription);
            }),
            'start_date' => Carbon::parse($this->start_date)->toDateString(),
            'end_date' => Carbon::parse($this->end_date)->toDateString(),
//            'start_date' => $this->start_date ? $this->start_date->toDateString() : null,
//            'end_date' => $this->end_date ? $this->end_date->toDateString() : null,
//            'created_at' => $this->created_at ? $this->created_at->toDateString() : null,
//            'updated_at' => $this->updated_at ? $this->updated_at->toDateString() : null,
        ];
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
//            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];    }

}
