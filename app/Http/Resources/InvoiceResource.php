<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->uuid,
            'status' => $this->status,
    //            'cost' => $this->cost,
            'provider_cost' => $this->provider_earning,
            'app_fess' => $this->admin_earning,
            'discount' => $this->discount,
            'sub_total' => $this->sub_total,
            'tax' => $this->tax,
            'total' => $this->total,

            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
//            'payment_id' => $this->payment_id,
//            'payment_url' => $this->payment_url,
//            'user_id' => $this->user_id,
//            'order_id' => $this->order_id,
//            'details' => $this->details,
            'details' => [
                'service' => $this->details['service'],
                'worker' => $this->details['worker'],
                'working_in_minutes' => $this->details['working_in_minutes'],
                'order_created_at' => Carbon::parse($this->details['order_created_at'])->format('Y-m-d H:i:s'),
            ],
            'qr_code_content' => $this->uuid,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
//            'order' => OrderResource::make($this->whenLoaded('order')),
            'order' => new OrderResource($this->whenLoaded('order')),


        ];
    }
}
