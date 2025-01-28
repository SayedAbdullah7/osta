<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LevelResource extends JsonResource
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
            'level' => $this->level,
            'orders_required' => $this->orders_required,
            'next_level_id' => $this->next_level_id,
            'is_current_level'=> $this->is_current_level,
            'statistics' =>$this->statistics,
            'next_level' => new LevelResource($this->whenLoaded('nextLevel')), // Load the related level
//            'created_at' => $this->created_at->toDateTimeString(),
//            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
