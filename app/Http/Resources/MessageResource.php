<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        $class = get_class(auth()->user());
        $userId = auth()->id();
        $orderId = $this->order_id;
        $invoiceId = $this->options['invoice_id'] ?? null;
        $response = [
            'id' => $this->id,
            'content' => $this->content,
            // 'conversation_id' => $this->conversation_id,
            // 'sender_id' => $this->sender_id,
            // 'sender_type' => $this->sender_type,
            // 'sender' => $this->sender_id == $userId && $this->sender_type == $class ? 'me' : 'other',
            "is_me" => (boolean)$this->sender_id == $userId && $this->sender_type == $class,
            'is_read' => (boolean)$this->is_read,
            'created_at' => date_format($this->created_at, 'Y-m-d H:i:s'),
            'media' => $this->getMedia('default')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                ];
            }),
            // is $this->options is empty
            'is_sys_msg' => !empty($this->options) ? true : false,
            // 'options' => $this->options
            'options' => [
                'variables' => $this->options['variables'] ?? null,
                'options' => $this->options['options'] ?? null,
                'url' => $this->options['url'] ?? null,
                'action_name' => $this->options['action_name'] ?? null,
                'action_status' => (string)($this->options['action_status'] ?? ''),
                'description' => $this->options['description'] ?? null,
                'invoice' => $this->options['invoice'] ?? null,
            ],
        ];

        // Add order details if order_id is not null
        if ($orderId) {
            $order = Order::with(['service', 'orderSubServices', 'subServices'])->find($orderId); // Eager load the relationships
            if ($order) {
                $response['order'] = new OrderResource($order); // Use OrderResource to format the order
            }
        }
        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
//            $order = Order::with(['service', 'orderSubServices', 'subServices'])->find($orderId); // Eager load the relationships
            if ($invoice) {
                $response['invoice'] = new InvoiceResource($invoice);
            }
        }

        return $response;
    }
}
