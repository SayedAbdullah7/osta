<?php

namespace App\Http\Resources;

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

        return [
            'id' => $this->id,
            'content' => $this->content,
//            'conversation_id' => $this->conversation_id,
//            'sender_id' => $this->sender_id,
//            'sender_type' => $this->sender_type,
//            'sender' => $this->sender_id == $userId && $this->sender_type == $class ? 'me' : 'other',
            "is_me" => (boolean)$this->sender_id == $userId && $this->sender_type == $class,
            'is_read' => (boolean)$this->is_read,
            'created_at' => $this->created_at,
            'media' => $this->getMedia('media')->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                ];
            }),
        ];
    }
}
