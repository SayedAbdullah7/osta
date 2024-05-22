<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'amount' => $this->amount,
            'type' => $this->type,
            'description'=> $this->meta['description'] ?? null,
            'created_at' => date_format($this->created_at, 'Y-m-d H:i:s'),
        ];
    }
}
