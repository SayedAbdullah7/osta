<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Http\Requests\MessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\ConverstionResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Provider;
use App\Repositories\MessageRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageService
{
    protected $messageRepository;
    protected $userOrderService;

    public function __construct(MessageRepository $messageRepository, UserOrderService $userOrderService)
    {
        $this->messageRepository = $messageRepository;
        $this->userOrderService =  $userOrderService;
    }

    /**
     * @throws Exception
     */
    public function getMessages($perPage, $page, $conversationId, $orderId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
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

    public function getMessagesWithConversation($perPage, $page, $conversationId, $orderId)
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

    /**
     * @throws \JsonException
     */
    public function applyCrearteMessage($conversation, $content, $senderId, $senderType, $media = null, $options = [], $orderId = null): \App\Models\Message
    {
        $conversationId = $conversation->id;

        $message = $this->messageRepository->createMessage($conversationId, $content, $senderId, $senderType, $options,$orderId);
//        Storage::put('message1.json', json_encode([$message]));
        $this->handleMedia($message, $media);
        $this->pushToSocket($message, $conversation);


//        $this->pushConversationToSocket($message, $conversation);


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
        if ($action == 'pay') {
            $price = $conversation->model->price;
            $actionMessage = $this->getPayMessage($price);
        }
        if ($action == 'cash_payment') {
            $price = $conversation->model->price;
            $actionMessage = $this->getCashPaymentMessage($price);
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
//        if ($this->checkSender($message, auth()->id(), get_class(auth()->user()))) {
//            $this->notFoundException('you can not response your own message');
//        }
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
        $action = $data['action_name'];
        $senderId = auth()->id();
        $senderType = get_class(auth()->user());
        if ($responseValue == 0) {
            return DB::transaction(function () use ($action, $message, $senderId, $senderType,$order) {
                $msg = 'rejected';
                if ($action == Message::ACTION_CONFIRM_ORDER ){
                    $this->userOrderService->cancelOrder($order);
                    $msg ='cancelled';
                }
                $options = $message->options;
                $options['action_status'] = 0;
                $message->options = $options;
                $message->save();
                return $this->applyCrearteMessage($message->conversation, 'rejected', $senderId, $senderType);
            });
        }
        if ($action == Message::ACTION_CONFIRM_ORDER ){
                    $this->userOrderService->confirmOrder($order);
        }


        if ($action == Message::ACTION_ADDITIONAL_COST) {
            $additionalCost = $data['variables']['x']??null;
            $walletService = app(WalletService::class);
            $invoice = $order->invoice;
            if (!$invoice) {
                $walletService->createInvoice($order);
            }
            if ($invoice->payment_status == 'paid') {
                $this->notFoundException('this action is not available');
            }
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
            $order->price += $additionalCost;
            $order->save();
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
        }

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
    public function getCashPaymentMessage($price): array
    {
        $actions = [
            'cash_payment' => [
                'message' => 'user_request_cash_payment',
                'info' => [
                    'variables' => [
                        'x' => $price
                    ],
                    'options' => [
                        ['name' => 'accept', 'value_response' => '1'],
                        ['name' => 'reject', 'value_response' => '0'],
                    ],
                    'url' => 'api/reponse-action',
                    'action_name' => 'cash_payment',
                    'action_status' => '1'
                ],
            ],

        ];
        return $actions['cash_payment'];
    }
    public function getWelcomeMessage(): array
    {
        $actions = [
            'confirm_order' => [
                'message' => 'welcome_message',
                'info' => [
//                    'variables' => [
//                        'x' => $additional_cost
//                    ],
                    'options' => [
                        ['name' => 'accept', 'value_response' => '1'],
                        ['name' => 'cancel', 'value_response' => '0'],
                    ],
                    'url' => 'api/reponse-action',
                    'action_name' => Message::ACTION_CONFIRM_ORDER,
                    'action_status' => '1'
                ],
            ],

        ];
        return $actions['confirm_order'];
    }
    public function getPayMessage($price): array
    {
        $actions = [
            'additional_cost' => [
                'message' => 'pay_order',
                'info' => [
                    'variables' => [
                        'x' => $price
                    ],
                    'options' => [
//                        ['name' => 'accept', 'value_response' => '1'],
//                        ['name' => 'reject', 'value_response' => '0'],
                    ],
//                    'url' => 'api/reponse-action',
                    'url' => '',
                    'action_name' => Message::ACTION_PAY,
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

    public function geAvailableOrderConversationsListWithTheOtherMember(int $page=1,int $perPage=10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $user = auth()->user();
        return $this->messageRepository->geAvailableOrderConversationsListWithTheOtherMemberFor($user,$page,$perPage);
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

    /**
     * @throws \JsonException
     */
    private function pushToSocket($message, $conversation): void
    {
//        $message = $message->refresh();

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
//        Storage::put('message2.json', json_encode([$messageClone], JSON_THROW_ON_ERROR));

        $socketService = new SocketService();
//        $room = 'conversation';
//        log::channel('test')->info('users and providers',[$users, $providers]);
        if (!empty($users)) {
//            log::channel('test')->info('users',$users);
            $socketService->push('user', $data, $users, $event, $msg);
        }
        if (!empty($providers)) {
//            log::channel('test')->info('providers',$providers);
            $socketService->push( 'provider', $data, $providers, $event, $msg);
        }
        if (get_class(auth()->user()) == Provider::class){
            $conversation->loadMissing('lastMessage.media','providers.media');
            $conversation->sender_id = null;
            $socketService->push('user', new ConverstionResource($conversation), $users, 'chat_message_in_conversation', $msg);

        }else{
            $conversation->loadMissing('lastMessage.media','users.media');
            $conversation->sender_id = null;
            $socketService->push('provider', new ConverstionResource($conversation), $providers, 'chat_message_in_conversation', $msg);
        }

    }

    private function notFoundException($message): void
    {
        throw new NotFoundHttpException($message);
    }


}
