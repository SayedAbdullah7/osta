<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class MessageResource extends JsonResource
{
    protected mixed $userId;

    public function __construct($resource, $userId = null)
    {
        parent::__construct($resource);
        $this->userId = $userId;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content??'',
            'sender_id' => $this->sender_id,
            'is_sender' => $this->sender_id === $this->userId,  // Check if authenticated user is the sender
            'short_name' => $this->sender?->short_name,  // Assuming you have a sender relationship
//            'media' => $this->media->map(function ($media) {
//                return [
//                    'id' => $media->id,
//                    'url' => $media->getUrl(),  // Assuming getUrl() method exists
//                    'thumb' => $media->getUrl('thumb'),  // Assuming 'thumb' is a valid size
//                ];
//            }),
            'media' => $this->media->filter(function ($media) {
                // Use the helper function to check if media is an image
                return $this->isImage($media);
            })->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                ];
            }),
        ];
    }

    // In a helper file or class
    function isImage($media)
    {
        // You can customize this logic based on the properties of the media
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $fileExtension = pathinfo($media->getUrl(), PATHINFO_EXTENSION);

        return in_array(strtolower($fileExtension), $imageExtensions);
    }

}
