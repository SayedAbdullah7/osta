<?php

namespace App\Services;

use App\Enums\OfferStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Requests\MessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\ConverstionResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Provider;
use App\Repositories\MessageRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
    public function markMessagesAsRead($conversationId, $user)
    {
        $userId = $user->id;
        $userType = get_class($user);
        \App\Models\Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->where('sender_type', '!=', $userType)
            ->where('is_read', false)
            ->update(['is_read' => true]);
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

    public function createConversationForModel(Model $model, $members, $startMessage = null,$sender = null): Conversation|null
    {
        $conversation = $model->conversation;
        if (!$conversation) {
            $conversation = $this->messageRepository->createConversationForModel($model);
            $this->messageRepository->addMembersToConversation($conversation, $members);
//            $conversation->participants()->attach($order->user_id);

            if ($startMessage) {
                if ($sender && $sender->id) {
                    $this->messageRepository->createMessage($conversation->id, $startMessage, $sender->id, get_class($sender));
                }else{
                    $this->messageRepository->createMessage($conversation->id, $startMessage);
                }
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
        $socketService = new SocketService();

//        dispatch(fn() => $socketService->push('user', $data, $users, $event, $msg));

        $message = $this->messageRepository->createMessage($conversationId, $content, $senderId, $senderType, $options,$orderId);
//        Storage::put('message1.json', json_encode([$message]));
        $this->handleMedia($message, $media);
        $this->pushToSocket($message, $conversation);


//        $this->pushConversationToSocket($message, $conversation);


//        Storage::put('message3.json', json_encode([$message]));
        return $message;
    }

    /**
     * @throws ValidationException
     * @throws \JsonException
     * @return \App\Models\Message|null Returns null if message sending is disabled (temporary for additional_cost, purchases, convert_to_offer, convert_to_preview)
     */
    public function makeAction($conversationId, $orderId, $action, $actionValue,string $description = '',$media = null)
    {
        $conversation = $this->getConversation($conversationId, $orderId);
        if (!$conversation) {
            $this->notFoundException('conversation not found');
        }
        if (!$conversation->is_active) {
            $this->notFoundException('conversation is not active');
        }
        $conversationId = $conversation->id;
//        if ($action == 'get_invoice') {
//            $order = $conversation->model;
//            $walletService = app(WalletService::class);
//            $invoice = $order->invoice ?? $walletService->createInvoice($order);
//            if (!$invoice->is_sent) {
//                $invoice->is_sent = true;
//                $invoice->save();
//            }
//
//            $actionMessage = $this->getInvoiceMessage($invoice->id??null);
//        }
        // TEMPORARY: Disabled message sending for these actions
        // TODO: Re-enable message sending when needed
        if ($action == 'additional_cost') {
            $order = $conversation->model;
            $additional_cost = $actionValue;
            // Execute the action but don't send message
            $this->handelAddtionalCost($order, (int)$additional_cost,$description);
            // Return null to skip message creation
            return null;
        }
        if ($action == 'purchases') {
            $order = $conversation->model;
            // Execute the action but don't send message
            $this->handelPurchases($order, (int)$actionValue,$description,$media);
            // Return null to skip message creation
            return null;
        }
        if ($action == 'pay') {
            if ($conversation->model->isPreview()) {
                $price = WalletService::PREVIEW_COST;
            }else{
                $price = $conversation->model->price;
            }
            $order = $conversation->model;
            $walletService = app(WalletService::class);
            $invoice = $order->invoice ?? $walletService->createInvoice($order);
            if (!$invoice->is_sent) {
                $invoice->is_sent = true;
                $invoice->save();
            }
            $actionMessage = $this->getPayMessage($price,$invoice);
        }
        if ($action == 'cash_payment') {
            $price = $conversation->model->price;
            $actionMessage = $this->getCashPaymentMessage($price);
        }
        // TEMPORARY: Disabled message sending for these actions
        // TODO: Re-enable message sending when needed
        if ($action == Message::ACTION_CONVERT_TO_OFFER) {
            $order = $conversation->model;
            if(!$order->isPreview()) {
                $this->notValidException('order already has an offer');
            }
            $offer = $actionValue;
            // Execute the action but don't send message
            $this->handelConvertToOffer($order, (int)$offer);
            // Return null to skip message creation
            return null;
        }
        if ($action == Message::ACTION_CONVERT_TO_PREVIEW) {
            $order = $conversation->model;
            if($order->isPreview()) {
                $this->notValidException('order already is preview');
            }
            // Execute the action but don't send message
            $this->handelConvertToPreview($order);
            // Return null to skip message creation
            return null;
        }
        if (!isset($actionMessage)) {
            $this->notFoundException('action not found');
        }
        $senderId = auth()->id();
        $senderType = get_class(auth()->user());
        $content = $actionMessage['message'];
        $options = $actionMessage['info'];
        return $this->applyCrearteMessage($conversation, $content, $senderId, $senderType, $media, $options);
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
                $invoice = $walletService->createInvoice($order);
            }
            if ($invoice->payment_status == 'paid') {
                $this->notFoundException('this action is not available');
            }
//            $walletService->updateInvoiceAdditionalCost($invoice, $order);
            $order->price += $additionalCost;
            $order->save();
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
        }
        if ($action == Message::ACTION_CONVERT_TO_OFFER) {
            $price = $data['variables']['x']??null;
            $walletService = app(WalletService::class);
            $invoice = $order->invoice;
            if (!$invoice) {
                $invoice = $walletService->createInvoice($order);
            }
            if ($invoice->payment_status == 'paid') {
                $this->notFoundException('this action is not available');
            }
            if(!$order->isPreview()) {
                $this->notValidException('order already has an offer');
            }
            if (!$price){
                $this->notValidException('offer price not valid');
            }
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
            $order->price = $price;
            $order->unknown_problem = 0;
            $order->save();
            $offer = $order->offers()->accepted()->first();
            if (!$offer) {
                $this->notValidException('offer not found');
            }
            $offer->price = $price;
            $offer->save();
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
        }
        if ($action == Message::ACTION_CONVERT_TO_PREVIEW) {
            // Use the dedicated method for better code organization
            $walletService = app(WalletService::class);
            $invoice = $order->invoice;
            if (!$invoice) {
                $invoice = $walletService->createInvoice($order);
            }
            if ($invoice->payment_status == 'paid') {
                $this->notFoundException('this action is not available');
            }
            if($order->isPreview()) {
                $this->notValidException('order already is preview');
            }

            // Use Order model method for easier maintenance
            $order->convertToPreview();

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

//    public function checkSender($message, $senderId, $senderType): bool
//    {
//        if ($message->sender_id != $senderId && $message->sender_type != $senderType) {
//        if ($message->sender_id != $senderId && $message->sender_type != $senderType) {
//            return true;
//        }
//        return false;
//    }
//
    public function getInvoiceMessage($invoiceId): array
    {
        $actions = [
                'message' => 'invoice',
                'info' => [
                    'invoice_id'=>$invoiceId
                ],
        ];
        return $actions;
    }

        public function getAdditionalCostMessage($additional_cost,$description): array
    {
        $actions = [
            'additional_cost' => [
                'message' => 'provider_request_additional_cost_equals '.$additional_cost,
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
                    'action_status' => '1',
                    'description' => $description
                ],
            ],

        ];
        return $actions['additional_cost'];
    }
    public function getAdditionalCostAddedMessage($additional_cost,$description,$invoice): array
    {
        return [
                'message' => 'provider_added_additional_cost_equals '.$additional_cost,
                'info' => [
                    'variables' => [
                        'x' => $additional_cost
                    ],
                    'options' => [
                    ],
                    'url' => '',
                    'action_name' => 'additional_cost',
                    'action_status' => '0',
                    'description' => $description,
                    'invoice' => new InvoiceResource($invoice)
                ],
        ];
    }
    public function getPurchasesCostAddedMessage($additional_cost,$description,$invoice): array
    {
        return [
                'message' => 'provider_added_purchases_equals '.$additional_cost,
                'info' => [
                    'variables' => [
                        'x' => $additional_cost
                    ],
                    'options' => [
                    ],
                    'url' => '',
                    'action_name' => 'purchases',
                    'action_status' => '0',
                    'description' => $description,
                    'invoice' => new InvoiceResource($invoice)
                ],
        ];
    }

    public function getConvertToOfferMessage($offerPrice,$invoice): array
    {
        return [
                'message' => 'provider_request_convert_to_offer_equals '.$offerPrice,
                'info' => [
                    'variables' => [
                        'x' => $offerPrice
                    ],
                    'options' => [
                        ['name' => 'accept', 'value_response' => '1'],
                        ['name' => 'reject', 'value_response' => '0'],
                    ],
                    'url' => 'api/reponse-action',
                    'action_name' => Message::ACTION_CONVERT_TO_OFFER,
                    'action_status' => '1',
                    'invoice' => new InvoiceResource($invoice)
                ],
        ];
    }

    public function getConvertToPreviewMessage(): array
    {
        return [
            'message' => 'provider_request_convert_to_preview',
            'info' => [
                'variables' => [
                ],
                'options' => [
                    ['name' => 'accept', 'value_response' => '1'],
                    ['name' => 'reject', 'value_response' => '0'],
                ],
                'url' => 'api/reponse-action',
                'action_name' => Message::ACTION_CONVERT_TO_PREVIEW,
                'action_status' => '1'
            ],
        ];
    }
    public function getConvertedToPreviewMessage($invoice): array
    {
        return [
            'message' => 'provider_converted_the_order_to_preview',
            'info' => [
                'variables' => [
                ],
                'options' => [
                ],
                'url' => '',
                'action_name' => Message::ACTION_CONVERT_TO_PREVIEW,
                'action_status' => '0',
                'invoice' => new InvoiceResource($invoice)
            ],
        ];
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
    public function getPayMessage($price,$invoice): array
    {
        $actions = [
            'additional_cost' => [
                'message' => 'pay_order '.$price,
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
                    'action_status' => '1',
                    'invoice' => new InvoiceResource($invoice)
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
            if (!empty($users)) {
                $conversation->loadMissing('lastMessage.media', 'providers.media');
                $conversation->sender_id = null;
                // Fix is_me for last_message in conversation
                if ($conversation->lastMessage) {
                    $lastMessageClone = clone $conversation->lastMessage;
                    $lastMessageClone->sender_id = null;
                    $conversation->setRelation('lastMessage', $lastMessageClone);
                }
                $socketService->push('user', new ConverstionResource($conversation), $users, 'chat_message_in_conversation', $msg);
            }

        }else{
            if (!empty($providers)){
                $conversation->loadMissing('lastMessage.media','users.media');
                $conversation->sender_id = null;
                // Fix is_me for last_message in conversation
                if ($conversation->lastMessage) {
                    $lastMessageClone = clone $conversation->lastMessage;
                    $lastMessageClone->sender_id = null;
                    $conversation->setRelation('lastMessage', $lastMessageClone);
                }
                $socketService->push('provider', new ConverstionResource($conversation), $providers, 'chat_message_in_conversation', $msg);
            }

        }

//        $conversation->loadMissing('lastMessage.media','users.media');
//        $conversation->sender_id = null;
//        $socketService->push('admin', $message, $providers, 'chat_message_in_conversation', $msg);

    }

    private function notFoundException($message): void
    {
        throw new NotFoundHttpException($message);
    }

    private function notValidException(string $message): void
    {
        throw ValidationException::withMessages([
            'msg' => [$message],
        ]);
    }

    /**
     * Add additional cost to order.
     * If order is preview (unknown_problem = 1), converts it to offer order.
     * If value is 0, removes additional_cost and converts back to preview if applicable.
     */
    private function handelAddtionalCost(Order $order, int $value = 0,$description = '')
    {
        // Only validate if adding a positive value to a preview order
        // Value 0 is allowed (it removes additional_cost and converts back to preview)
        if($order->unknown_problem == 1 && $value > 0 && $value <= \App\Models\Setting::getPreviewCost()){
            $this->notValidException('this value is not valid for this order');
        }

        $walletService = app(WalletService::class);
        $invoice = $order->invoice;
        if (!$invoice) {
            $invoice = $walletService->createInvoice($order);
        }

        // Use Order model method for easier maintenance
        // This handles both adding and removing (when value = 0)
        $order->addAdditionalCost($value, $description);

        $invoice = $walletService->updateInvoiceAdditionalCost($invoice, $order);
        return $this->getAdditionalCostAddedMessage($value, $description, $invoice);
        }
    /**
     * Add purchases to order.
     * Purchases are added separately and don't convert preview to offer.
     * Uses Order model method for easier maintenance.
     */
    private function handelPurchases(Order $order, int $value = 0,$description = '',$media = null)
    {
        $walletService = app(WalletService::class);
        $invoice = $order->invoice;
        if (!$invoice) {
            $invoice = $walletService->createInvoice($order);
        }

        // Use Order model method for easier maintenance
        $orderDetail = $order->addPurchases($value, $description);

        // Attach media if provided
        if ($media){
            foreach ($media as $image) {
                $orderDetail->addMedia($image)->preservingOriginal()->toMediaCollection('images');
            }
        }

        $invoice = $walletService->updateInvoiceAdditionalCost($invoice, $order);
        return $this->getPurchasesCostAddedMessage($value, $description, $invoice);
    }

    /**
     * Handle conversion of offer order to preview order.
     * Uses Order model method for easier maintenance.
     */
    private function handelConvertToPreview(Order $order): ?array
    {
        $walletService = app(WalletService::class);
        $invoice = $order->invoice;
        if (!$invoice) {
            $invoice = $walletService->createInvoice($order);
        }
        if($order->isPreview()) {
            $this->notValidException('order already is preview');
        }

        // Use Order model method for easier maintenance
        $order->convertToPreview();

        $invoice = $walletService->updateInvoiceAdditionalCost($invoice, $order);
        return $this->getConvertedToPreviewMessage($invoice);
    }

    /**
     * @throws ValidationException
     */
    /**
     * Convert preview order to offer order with price.
     * Removes preview_cost OrderDetail and sets offer price.
     */
    private function handelConvertToOffer(Order $order, $price): ?array
    {
        $walletService = app(WalletService::class);
        $invoice = $order->invoice;
        if (!$invoice) {
            $invoice = $walletService->createInvoice($order);
        }
        if ($invoice->payment_status == 'paid') {
            $this->notFoundException('this action is not available');
        }
        if(!$order->isPreview()) {
            $this->notValidException('order already has an offer');
        }
        if (!$price){
            $this->notValidException('offer price not valid');
        }

        // Use Order model method for easier maintenance
        $order->convertToOffer($price);

        $walletService->updateInvoiceAdditionalCost($invoice, $order);
        return  $this->getConvertToOfferMessage($price, $invoice);
    }


}
