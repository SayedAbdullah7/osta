<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Resources\MessageResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Services\MessageService;
use App\Services\SocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    use ApiResponseTrait;

    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * @throws \Exception
     */
    public function index(MessageRequest $request)
    {

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $messages = $this->messageService->getMessages($perPage, $page, $request->conversation_id, $request->order_id);
        return $this->respondWithResource(MessageResource::collection($messages), 'Messages retrieved successfully');


        $conversation = $this->getConversation($request->conversation_id, $request->order_id);
        if (!$conversation) {
            return $this->respondNotFound('conversation not found');
        }

        if (!$conversation->is_active) {
            return $this->respondNotFound('conversation is not active');
        }

        $class = get_class(auth()->user());
        $userId = auth()->id();

        $messages = Cache::remember($userId . $class . 'messages', 1, function () use ($conversation, $userId, $class, $perPage, $page) {
            $conversation->messages()->where('sender_id', '!=', $userId)->where('sender_type', '!=', $class)->update(['is_read' => 1]);
            return $conversation->messages()->orderBy('id', 'desc')->simplePaginate($perPage,['*'],'page',$page);

//            $messages = $conversation->messages;
            //update all messages to read where not belong to auth user ( morph relation between message and user and provider )
        });


        return $this->respondWithResource(MessageResource::collection($messages), 'Messages retrieved successfully');
    }

//    public function sendMessage(StoreMessageRequest $request)
//    {
//        $conversation = $this->getConversation($request);
//        if (!$conversation->is_active) {
//            return $this->respondNotFound('conversation is not active');
//        }
//
//        $message = new Message();
//        $message->content = $request->input('content');
//        $message->conversation_id = $request->input('conversation_id');
//        $message->sender_id = auth()->id();
//        $message->sender_type = get_class(auth()->user());
//        $message->save();
//        $conversation->members;
//        $users = [];
//        $providers = [];
//        foreach ($conversation->members as $member) {
//            if ($member->user_id == $member->sender_id && $member->user_type == $member->sender_type) {
//                continue;
//            }
//            if ($member->user_type == 'App\Models\User') {
//                $users[] = $member->user_id;
//            } elseif ($member->user_type == 'App\Models\Provider') {
//                $providers[] = $member->user_id;
//            }
//
//        }
//
//
//
//        $socketService = new SocketService();
//        $data2 = new MessageResource($message);
//
////        $data = ['content' => $message->content,'id' => $message->content];
//
////        $users = [1, 2, 3]; // Replace with your actual user IDs
////        $providers = [1, 2, 3]; // Replace with your actual provider IDs
//        $to = ['users' => $users, 'providers' => $providers];
////        return json_encode($to);
////        $to = json_encode($to);
//        $event = 'chat_message';
//        $msg = "you have a new message from " . auth()->user()->name . " in " . $conversation->name . " chat";
//
//
//        return $response = $socketService->push('user',$data2, $to, $event, $msg);
//
////        return $this->respondSuccess('Message sent successfully');
//    }
    /**
     * @throws \Exception
     */
    public function sendMessage(StoreMessageRequest $request): \Illuminate\Http\JsonResponse
    {
        $message = $this->messageService->createMessage($request->conversation_id, $request->order_id, $request->content, $request->media);
        return $this->respondWithResource(new MessageResource($message), 'Message sent successfully');
    }
//        $conversation = $this->getConversation($request->conversation_id, $request->order_id);
//        if (!$conversation) {
//            return $this->respondNotFound('conversation not found');
//        }
//        if (!$conversation->is_active) {
//            return $this->respondNotFound('conversation is not active');
//        }
//
//        $message = $this->createMessage($request, $conversation);
//
//        $this->handleMedia($request, $message);
//
//        $this->pushToSocket($message, $conversation);
//
//        return $this->respondWithResource(new MessageResource($message), 'Message sent successfully');


    /**
     * @param $request
     * @param $conversation
     * @return Message
     */
    private function createMessage($request, $conversation): Message
    {
        $message = new Message();
        $message->content = $request->input('content');
        $message->conversation_id = $conversation->id;
        $message->sender_id = auth()->id();
        $message->sender_type = get_class(auth()->user());
        $message->save();

        return $message;
    }

    private function handleMedia($request, $message): void
    {

        if ($request->hasFile('media')) {
            $media = $request->file('media');
//            $mediaName = time() . '.' . $media->getClientOriginalExtension();
//            $mediaType = $media->getMimeType();
//
//            if (str_starts_with($mediaType, 'image')) {
//                $media->move(public_path('images'), $mediaName);
//                $message->image = '/images/' . $mediaName;
//            } else if (str_starts_with($mediaType, 'video')) {
//                $media->move(public_path('videos'), $mediaName);
//                $message->video = '/videos/' . $mediaName;
//            }
            foreach ($media as $file){
                $mediaItem = $message->addMedia($file)->toMediaCollection('media');
            }

//            if ($mediaItem->hasGeneratedConversion('thumb')) {
//                $message->thumbnail_url = $mediaItem->getUrl('thumb');
//            }
//
//            $message->media_url = $mediaItem->getUrl();
//            $message->save();
        }
    }

    private function pushToSocket($message, $conversation): void
    {
        $socketService = new SocketService();
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

        $socketService->push('provider',$data, $providers, $event, $msg);
        $socketService->push('user',$data, $users, $event, $msg);

    }
    private function getConversation( $conversationId = null,  $orderId = null): ?Conversation
    {
        if (!$conversationId) {
            $order = Order::find($orderId);
            $conversation = $order->conversation;
        }elseif($orderId) {
            $conversation = Conversation::find($conversationId);
        }
        return $conversation??null;
    }
}
