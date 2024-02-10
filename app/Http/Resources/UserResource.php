<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
//            'account' => $this->account,
            'gender' => $this->gender?'male':'female',
            'token' => $this->token,
            'country' => new CountryResource($this->whenLoaded('country')), // Conditionally include 'country' if it's loaded
        ];
    }
}
