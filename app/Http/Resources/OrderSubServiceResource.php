<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderSubServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->subService->id,
//            'order_id' => $this->order_id,
//            'sub_service_id' => $this->sub_service_id,
//            'space_id' => $this->space_id,
            'name' => $this->subService->name,
            'max_price' =>$this->max_price,
            'type' => $this->subService->type,
            'quantity' => $this->quantity,
            'space_name' => $this->whenLoaded('space')?$this->space->name:$this->space_name,
            'order' => new OrderResource($this->whenLoaded('order')),
            'sub_service' => new SubServiceResource($this->whenLoaded('sub_service')),
//            "space"=> $this->space,
//            'space' => new SpaceResource($this->whenLoaded('space')),
        ];
    }

}
