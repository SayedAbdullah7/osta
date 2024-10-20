<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyResource extends JsonResource
{
    protected $orderCreatedAt;

    public function __construct($resource, $orderCreatedAt = null)
    {
        parent::__construct($resource);
        $this->orderCreatedAt = $orderCreatedAt;
    }
//    public function foo($orderCreatedAt){
//        $this->orderCreatedAt = $orderCreatedAt;
//        return $this;
//    }

    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_months' => $this->duration_months,
            'percentage_cost' => $this->percentage_cost,
        ];

        // Only add expiration_date if orderCreatedAt is provided
        if ($this->orderCreatedAt) {
//            $data['expiration_date'] = $this->orderCreatedAt;
            $expirationDate = $this->orderCreatedAt->copy()->addMonths($this->duration_months);
            $data['expiration_date'] = $expirationDate->toDateString();
        }

        return $data;
    }
}
