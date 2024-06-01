<?php

namespace App\Services;

use App\Http\Requests\MessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Repositories\MessageRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageService
{
    protected $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * @throws Exception
     */
    public function getMessages($perPage, $page, $conversationId, $orderId): \Illuminate\Pagination\Paginator
    {
        if (!$conversationId) {
            $conversation = $this->messageRepository->getConversationIdByModelId($orderId);
            $conversationId = $conversation->id;
        } else {
            $conversation = $this->messageRepository->getConversationIdById($conversationId);
        }
        if (!$conversation) {
            $this->notFoundException('conversation not found');
        }
        if (!$conversation->is_active) {
            $this->notFoundException('conversation is not active');
        }

        return $this->messageRepository->getSimplePaginateMessagesByConversationId($perPage, $page, $conversationId);//    return $this->messageRepository->getMessages($request);
    }

    public function createConversationForModel(Model $model,$startMessage = null): Conversation|null
    {
        $conversation = $model->conversation;
        if (!$conversation) {
            $conversation = $this->messageRepository->createConversationForModel($model);

//            $conversation->participants()->attach($order->user_id);

            if ($startMessage) {
                $this->messageRepository->createMessage($conversation, $startMessage);
//                $conversation->messages()->create([
//                    'content' => $startMessage,
////                    'content' => 'Order accepted, number #' . $model->id,
////                'sender_id' => $order->user_id,
////                'sender_type' => get_class($order->provider),
//                ]);
            }
        }
        return $conversation;
    }

    public function createConversationForType(string $type, $startMessage = null): Conversation|null
    {
        $conversation = $this->messageRepository->createConversationForType($type);

        if ($startMessage) {
            $this->messageRepository->createMessage($conversation, $startMessage);
//            $conversation->messages()->create([
//                'content' => $startMessage,
//            ]);
        }
        return $conversation;
    }




    /**
     * @throws Exception
     */
    public function createMessage($conversationId, $orderId, $content, $media): \App\Models\Message
    {
        $conversation = $this->getConversation($conversationId, $orderId);

        if (!$conversation) {
            $this->notFoundException('conversation not found');
        }
        if (!$conversation->is_active) {
            $this->notFoundException('conversation is not active');
        }
        $senderId = auth()->id();
        $senderType = get_class(auth()->user());
        $message = $this->messageRepository->createMessage($conversationId, $content , $senderId, $senderType);

        $this->handleMedia($message, $media);
        $this->pushToSocket($message, $conversation);
        return $message;

    }

    /**
     * @param $conversationId
     * @param $orderId
     * @return Conversation|null
     */
    public function getConversation($conversationId, $orderId): Conversation|null
    {
        if (!$conversationId) {
            $conversation = $this->messageRepository->getConversationIdByModelId($orderId);
        } else {
            $conversation = $this->messageRepository->getConversationIdById($conversationId);
        }
        return $conversation;
    }

    private function handleMedia($message, $files,): void
    {
//            if ($request->hasFile('media')) {
//                $files = $request->file('media');
        foreach ($files as $file) {
            $this->messageRepository->addMedia($file, $message);
        }
//            }
    }

    private function pushToSocket($message, $conversation): void
    {
        $data = new MessageResource($message);
        $event = 'chat_message';
        $msg = "you have a new message from " . auth()->user()->name . " in " . $conversation->name . " chat";

        $users = [];
        $providers = [];
        foreach ($conversation->members as $member) {
            if ($member->user_id == $member->sender_id && $member->user_type == $member->sender_type) {
                continue;
            }
            if ($member->user_type == 'App\Models\User') {
                $users[] = $member->user_id;
            } elseif ($member->user_type == 'App\Models\Provider') {
                $providers[] = $member->user_id;
            }
        }

        $to = ['users' => $users, 'providers' => $providers];

//        $socketService->push('user', $data, $to, $event, $msg);
        $socketService = new SocketService();
        $socketService->push('provider', $data, $providers, $event, $msg);
        $socketService->push('user', $data, $users, $event, $msg);

    }

    private function notFoundException($message): void
    {
        throw new NotFoundHttpException($message);
    }
}
