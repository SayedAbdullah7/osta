<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LevelResource extends JsonResource
{
    public $currentLevelId;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Determine which badge image to use based on level name
        $badgeImage = match(strtolower($this->name)) {
//            'bronze' => Storage::url('levels/bronz.svg'),
//            'silver' => Storage::url('levels/silver_medal.svg'),
//            'gold' => Storage::url('levels/gold_medal.svg'),

            'bronze' => asset('storage/levels/bronz.svg'),
            'silver' => asset('storage/levels/silver_medal.svg'),
            'gold'   => asset('storage/levels/gold_medal.svg'),
            default => null,
        };


        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'level' => $this->level,
            'badge_image' => $badgeImage,
//            'badge_image' => $this->badge_image_url,
            'requirements' => [
                'completed_orders' => $this->requirements['metrics']['completed_orders'] ?? 0,
                'average_rating' => $this->requirements['metrics']['average_rating'] ?? 0,
            ],
            'benefits' => $this->benefits,
            'is_current' => $this->whenLoaded('pivot', function () {
                return (bool) $this->pivot->is_current;
            }),
            'achieved_at' => $this->whenLoaded('pivot', function () {
                if (!$this->pivot->achieved_at) {
                    return null;
                }

                $date = new \DateTime($this->pivot->achieved_at);
                return $date->format('Y-m-d H:i:s');  // or any format you want
            }),
        ];
//        return [
//            'id' => $this->id,
//            'name' => $this->name,
//            'level' => $this->level,
//            'orders_required' => $this->orders_required,
//            'next_level_id' => $this->next_level_id,
//            'is_current_level'=> $this->is_current_level,
//            'statistics' =>$this->statistics,
//            'next_level' => new LevelResource($this->whenLoaded('nextLevel')), // Load the related level
////            'created_at' => $this->created_at->toDateTimeString(),
////            'updated_at' => $this->updated_at->toDateTimeString(),
//        ];
    }
}
