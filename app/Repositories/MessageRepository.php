<?php

namespace App\Repositories;

use App\Http\Requests\MessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class MessageRepository
{
    public function getSimplePaginateMessagesByConversationId($perPage, $page, $conversationId): \Illuminate\Pagination\Paginator
    {
        return Message::where('conversation_id', $conversationId)->orderBy('id', 'desc')->simplePaginate($perPage, ['*'], 'page', $page);
    }

    public function getSimplePaginateMessagesByOrderId($perPage, $page, $orderId): \Illuminate\Pagination\Paginator
    {
        return Message::whereHas('conversation', function ($query) use ($orderId) {
            $query->where('model_id', $orderId);
        })->orderBy('id', 'desc')->simplePaginate($perPage, ['*'], 'page', $page);
    }


    /**
     * @param $orderId
     * @return Conversation|null
     */
    public function getConversationIdByModelId($orderId): Conversation|null
    {
        return Conversation::where('model_id', $orderId)->first();
    }

    /**
     * @param $conversationId
     * @return Conversation|null
     */
    public function getConversationIdById($conversationId): Conversation|null
    {
        return Conversation::find($conversationId);
    }

    /**
     * @param $conversationId
     * @param $request
     * @return Message
     */
    public function createMessage($conversationId, $content): Message
    {
        $message = new Message();
        $message->content = $content;
        $message->conversation_id = $conversationId;
        $message->sender_id = auth()->id();
        $message->sender_type = get_class(auth()->user());
        $message->save();
        return $message;
    }

    public function addMedia($media,$message): \Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        return $message->addMedia($media)->toMediaCollection();
    }
}
