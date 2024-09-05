<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Http\Requests\MessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Repositories\MessageRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        } else {
            $conversation = $this->messageRepository->getConversationIdById($conversationId);
        }
        if (!$conversation) {
            $this->notFoundException('conversation not found');
        }
        if (!$conversation->is_active) {
            $this->notFoundException('conversation is not active');
        }
        $conversationId = $conversation->id;

        return $this->messageRepository->getSimplePaginateMessagesByConversationId($perPage, $page, $conversationId);//    return $this->messageRepository->getMessages($request);
    }

    public function getMessagesWithConversation($perPage, $page, $conversationId, $orderId): array
    {
        if (!$conversationId) {
            $conversation = $this->messageRepository->getConversationIdByModelId($orderId);
        } else {
            $conversation = $this->messageRepository->getConversationIdById($conversationId);
        }
        if (!$conversation) {
            $this->notFoundException('conversation not found');
        }
        if (!$conversation->is_active) {
            $this->notFoundException('conversation is not active');
        }
        $conversationId = $conversation->id;

        return [$this->messageRepository->getSimplePaginateMessagesByConversationId($perPage, $page, $conversationId), $conversation];//]/    return $this->messageRepository->getMessages($request);
    }

    public function createConversationForModel(Model $model, $members, $startMessage = null): Conversation|null
    {
        $conversation = $model->conversation;
        if (!$conversation) {
            $conversation = $this->messageRepository->createConversationForModel($model);
            $this->messageRepository->addMembersToConversation($conversation, $members);
//            $conversation->participants()->attach($order->user_id);

            if ($startMessage) {
                $this->messageRepository->createMessage($conversation->id, $startMessage);
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
        $conversationId = $conversation->id;
        $senderId = auth()->id();
        $senderType = get_class(auth()->user());
        return $this->applyCrearteMessage($conversation, $content, $senderId, $senderType, $media);
//        Storage::put('data.json', json_encode([$conversationId]));


    }

    private function applyCrearteMessage($conversation, $content, $senderId, $senderType, $media = null, $options = []): \App\Models\Message
    {
        $conversationId = $conversation->id;
        $message = $this->messageRepository->createMessage($conversationId, $content, $senderId, $senderType, $options);
//        Storage::put('message1.json', json_encode([$message]));
        $this->handleMedia($message, $media);
        $this->pushToSocket($message, $conversation);
//        Storage::put('message3.json', json_encode([$message]));
        return $message;
    }

    public function makeAction($conversationId, $orderId, $action, $actionValue): \App\Models\Message
    {
        $conversation = $this->getConversation($conversationId, $orderId);
        if (!$conversation) {
            $this->notFoundException('conversation not found');
        }
        if (!$conversation->is_active) {
            $this->notFoundException('conversation is not active');
        }
        $conversationId = $conversation->id;
        $additional_cost = $actionValue;
        if ($action == 'additional_cost') {
            $actionMessage = $this->getAdditionalCostMessage($additional_cost);
        }
        if (!isset($actionMessage)) {
            $this->notFoundException('action not found');
        }
        $senderId = auth()->id();
        $senderType = get_class(auth()->user());
        $content = $actionMessage['message'];
        $options = $actionMessage['info'];
        return $this->applyCrearteMessage($conversation, $content, $senderId, $senderType, null, $options);
//        $message = $this->messageRepository->createMessage($conversationId, $content, $senderId, $senderType, $options);
//        $this->pushToSocket($message, $conversation);

//        $this->messageRepository->createMessage($conversationId,
    }

    public function responseAction($messageId, $responseValue): \App\Models\Message
    {
        $message = $this->messageRepository->findById($messageId);
        if (!$message) {
            $this->notFoundException('message not found');
        }

        // check currently user is not sender
        if ($this->checkSender($message, auth()->id(), get_class(auth()->user()))) {
            $this->notFoundException('you can not response your own message');
        }
        $data = $message->options;
        if (!isset( $data['action_status'])){
            $this->notFoundException('no action found');
        }
        $status = $data['action_status'];
        if ($status == '0') {
            $this->notFoundException('this action is not available');
        }

        $conversation = $message->conversation;
        $order = $conversation->model;
        if ($conversation == 'order' && $order && $order->status == OrderStatusEnum::DONE) {
            $this->notFoundException('this action is not available');
        }
        $senderId = auth()->id();
        $senderType = get_class(auth()->user());
        if ($responseValue == 0) {
            return DB::transaction(function () use ($message, $senderId, $senderType) {
                $options = $message->options;
                $options['action_status'] = 0;
                $message->options = $options;
                $message->save();
                return $this->applyCrearteMessage($message->conversation, 'rejected', $senderId, $senderType);
            });
        }
//        $additionalCost = 30;
        $additionalCost = $data['variables']['x'];
        $action = $data['action_name'];
        $walletService = new WalletService();
        $invoice = $order->invoice;
        if (!$invoice) {
            $walletService->createInvoice($order);
        }
        if ($invoice->payment_status == 'paid') {
            $this->notFoundException('this action is not available');
        }
        if ($action == 'additional_cost') {
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
        }
        $order->price += $additionalCost;
        $order->save();
        $walletService->updateInvoiceAdditionalCost($invoice, $order);
        return DB::transaction(function () use ($message, $senderId, $senderType) {
            $options = $message->options;
            $options['action_status'] = 0;
            $message->options = $options;
            $message->save();
            return $this->applyCrearteMessage($message->conversation, 'accepted', $senderId, $senderType);
        });
    }

    public function checkSender($message, $senderId, $senderType): bool
    {
        if ($message->sender_id != $senderId && $message->sender_type != $senderType) {
            return true;
        }
        return false;
    }

    public function getAdditionalCostMessage($additional_cost): array
    {
        $actions = [
            'additional_cost' => [
                'message' => 'provider_request_additional_cost equals '.$additional_cost.'%',
                'info' => [
                    'variables' => [
                        'x' => $additional_cost
                    ],
                    'options' => [
                        ['name' => 'accept', 'value_response' => '1'],
                        ['name' => 'reject', 'value_response' => '0'],
                    ],
                    'url' => 'api/reponse-action',
                    'action_name' => 'additional_cost',
                    'action_status' => '1'
                ],
            ],

        ];
        return $actions['additional_cost'];
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

    public function geAvailableOrderConversationsListWithTheOtherMember()
    {
        $user = auth()->user();
        return $this->messageRepository->geAvailableOrderConversationsListWithTheOtherMemberFor($user);
//        $participant = $conversation->members()->where('user_id', '!=', auth()->id())->where('user_type', '!=', get_class(auth()->user()))->first();

    }

    private function handleMedia($message, $files,): void
    {
//            if ($request->hasFile('media')) {
//                $files = $request->file('media');
        if ($files) {
            foreach ($files as $file) {
                $this->messageRepository->addMedia($file, $message);
            }
        }

//            }
    }

    private function pushToSocket($message, $conversation): void
    {

        $event = 'chat_message';
        $msg = "you have a new message from " . auth()->user()->name . " in " . $conversation->name . " chat";

        $users = [];
        $providers = [];
        foreach ($conversation->members as $member) {
//            \Illuminate\Support\Facades\Storage::put(time().'member.json', json_encode([$member,$message]));

            if ($member->user_id == $message->sender_id && $member->user_type == $message->sender_type) {
//                \Illuminate\Support\Facades\Storage::put(time().'memberTrue.json', json_encode([$member]));
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

        // fix is me = true
        $messageClone = clone $message;
        $messageClone->sender_id = null;
        $data = new MessageResource($messageClone);
        Storage::put('message2.json', json_encode([$messageClone]));

        $socketService = new SocketService();
        $room = 'conversation.' . $conversation->id;
        if (!empty($users)) {
            $socketService->push($room . '.user', $data, $users, $event, $msg);
        }
        if (!empty($providers)) {
            $socketService->push($room . '.provider', $data, $providers, $event, $msg);
        }

    }

    private function notFoundException($message): void
    {
        throw new NotFoundHttpException($message);
    }


}
