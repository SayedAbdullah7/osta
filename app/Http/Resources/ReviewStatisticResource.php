<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewStatisticResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_reviews' => isset($this->total_reviews) ? (int) $this->total_reviews : 0,
            'average_rating' => isset($this->average_rating) ? (string) $this->average_rating : '0',
            'completed_orders' => isset($this->completed_orders) ? (int) $this->completed_orders : 0
        ];
    }
}
