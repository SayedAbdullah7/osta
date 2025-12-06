<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderLevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'current_level' => new LevelResource($this->getCurrentLevel()),
//            'current_level' => $this->whenLoaded('currentLevel', function () {
//                return new LevelResource($this->currentLevel);
//            }, null),
            'progress' => [
                'completed_orders' => $this->currentMonthMetrics->completed_orders,
                'required_orders' => optional($this->getCurrentLevel())->requirements['metrics']['completed_orders'] ?? 0,
                'average_rating' => $this->currentMonthMetrics->average_rating,
                'required_rating' => optional($this->getCurrentLevel())->requirements['metrics']['average_rating'] ?? 0,
//                'progress_percentage' => $this->calculateProgressPercentage(),
            ],
        ];
    }
}
