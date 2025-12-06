<?php

namespace App\Repositories;

use App\Http\Requests\MessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MessageRepository
{
    public function getSimplePaginateMessagesByConversationId($perPage, $page, $conversationId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
//        return Message::where('conversation_id', $conversationId)->orderBy('id', 'desc')->simplePaginate($perPage, ['*'], 'page', $page);
        return Message::with('media')->where('conversation_id', $conversationId)->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
    }

    public function getSimplePaginateMessagesByOrderId($perPage, $page, $orderId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Message::whereHas('conversation', function ($query) use ($orderId) {
            $query->where('model_id', $orderId);
        })->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
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

    public function createConversationForModel(Model $model): Conversation
    {
        $conversation = new Conversation();
        $conversation->model_id = $model->id;
        $conversation->model_type = \get_class($model);
        $conversation->type = strtolower(class_basename($model));
        $conversation->save();

        return $conversation;
    }

    public function createConversationForType($type): Conversation
    {
        $conversation = new Conversation();
        $conversation->type = $type;
        $conversation->save();
        return $conversation;
    }

//    public function createMessageForConversation(Conversation $conversation, $content): Message
//    {
//        $message = new Message();
//        $message->content = $content;
//        $message->conversation_id = $conversation->id;
//        $message->sender_id = auth()->id();
//        $message->sender_type = get_class(auth()->user());
//        $message->save();
//        return $message;
//
//    }

    /**
     * @param $conversationId
     * @param $request
     * @return Message
     */
    public function createMessage($conversationId, $content, $senderId = null, $senderType = null, $options = null,$orderId = null): Message
    {
        $message = new Message();
        $message->content = $content;
        $message->conversation_id = $conversationId;
        $message->sender_id = $senderId;
        $message->sender_type = $senderType;
//        $message->sender_id = auth()->id();
//        $message->sender_type = get_class(auth()->user());
        if ($options) {
            $message->options = $options;
        }
        if ($orderId) {
            $message->order_id = $orderId;
        }
        $message->save();
        return $message;
    }

    public function addMedia($media, $message): \Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        return $message->addMedia($media)->toMediaCollection();
    }

    public function addMembersToConversation(Conversation $conversation, $members): void
    {
        $memberIds = collect($members)->map(fn($member) => [
            'user_id' => $member->id,
            'user_type' => \get_class($member),
        ]);

        $conversation->members()->createMany($memberIds);
    }

    public function makeAction($conversationId, $actionMessage): void
    {
        $additional_cost = 0;
        $actions = [
            'message' => 'provider_request_additional_cost equals $x %',
            'variable' => [
                '$x' => $additional_cost
            ],
            'options' => [
                ['name' => 'accept', 'value' => '1'],
                ['name' => 'reject', 'value' => '0'],
            ]

        ];

    }

    public function findById($messageid): Message
    {
        return Message::find($messageid);
    }

    public function geAvailableOrderConversationsListWithTheOtherMemberFor($member,$page,$perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $orderIds = $member->orders()->availableForConversation()->pluck('id');
        if (get_class($member) == User::class) {
            $conversations = Conversation::with('lastMessage.media','model',
                'model.service',
                'model.orderSubServices',
                'model.subServices',
            )->where('model_type', Order::class)
                ->whereIn('model_id', $orderIds)
//                ->join('messages', 'conversations.id', '=', 'messages.conversation_id')
//                ->orderBy('messages.id', 'desc') // Order by the last message's id
//                ->select('conversations.*') // Select only conversation fields
                ->with(['providers.media'])
                ->orderBy(
                    Message::select('id')
                        ->whereColumn('conversation_id', 'conversations.id')
                        ->latest()
                        ->take(1),
                    'desc'
                )
                ->paginate($perPage, ['*'], 'page', $page);
        } else {
            $conversations = Conversation::with('lastMessage.media','model',
                'model.service',
                'model.orderSubServices',
                'model.subServices',
            )->where('model_type', Order::class)
                ->whereIn('model_id', $orderIds)
//                ->join('messages', 'conversations.id', '=', 'messages.conversation_id')
//                ->orderBy('messages.id', 'desc') // Order by the last message's id
//                ->select('conversations.*') // Select only conversation fields
                ->with(['users.media'])
                ->orderBy(
                    Message::select('id')
                        ->whereColumn('conversation_id', 'conversations.id')
                        ->latest()
                        ->take(1),
                    'desc'
                )
                ->paginate($perPage, ['*'], 'page', $page);
        }
        $conversations->transform(function ($conversation,$member) {
            $conversation->unread_messages_count = $conversation->unreadMessagesCountForUser(auth()->user());
            return $conversation;
        });
        return $conversations;
// Transform the structure
        $conversations->transform(function ($conversation,$member) {
            $conversation->profile = $conversation->members->first()->user;
            unset($conversation->members);
            return $conversation;
        });
        return $conversations;

//        return Conversation::where('model_type', Order::class)->whereHas('model', static function ($query) use ($member) {
//            $query->whereBelongsTo($member)->availableForConversation();
//        })->with(['members as profile' => static function ($query) use ($member) {
//            $query->where('user_id', '!=', $member->id)->where('user_type', '!=', \get_class($member))->first();
//        }])->get();
    }
}
