<?php

namespace App\Http\Controllers;

use App\DataTables\Custom\ConversationDataTable;
use App\DataTables\Custom\TicketDataTable;
use App\Http\Resources\Dashboard\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Order;
use App\Models\Ticket;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        if (!Auth::check()) {
            Auth::loginUsingId(1); // Assuming admin ID 1 is a valid admin
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(TicketDataTable $dataTable, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if ($request->ajax()) {
            return $dataTable->handle();
        }

        // Return view with dynamic columns and filters
        return view('pages.conversation.index', [
            'columns' => $dataTable->columns(),
            'filters' => $dataTable->filters(),
        ]);
    }

    public function getConversation(Request $request, $id)
    {
        $isOrderChat = $request->input('type') == 'order';
        if ($isOrderChat) {
            $order = Order::find($id);
            $conversation = $order->conversation;
            $conversation->user_id = $order->user_id;
            $conversation->provider_id = $order->provider_id;
//            $conversation = Conversation::where('model_id', $id)->first();
            return response()->json($conversation);

        } else {
            $user = Auth::user();
            $ticket = Ticket::with('user', 'conversation')->find($id);
//        $conversation = Conversation::find(217);
//        $ticket->setRelation('conversation', $conversation);
            if (!$ticket->name) {
                $ticket->name = 'Ticket #' . $ticket->id;
            }
            $ticket->short_name = $ticket->user->short_name;

            return response()->json($ticket);
        }
    }

    public function getMessages(Request $request, $conversationId)
    {
        $messages = Message::with('media', 'sender')->where('conversation_id', $conversationId)
            ->get();
        if($request->input('user_id')){
            $userId = $request->input('user_id');
        }else{
            $userId = Auth::id();
        }
//        $data = MessageResource::collection($messages)->additional(['user_id' => $userId]);
        $data = MessageResource::collection($messages->map(function ($message) use ($userId) {
            return new MessageResource($message, $userId);
        }));
        return response()->json($data);
    }

    /**
     * @throws \Exception
     */
    public function sendMessage(Request $request)
    {
        $conversationId = $request->input('conversation_id');
        $message = $this->messageService->createMessage($conversationId, null, $request->input('message'), $request->media);

//        $message = Message::create([
//            'content' => $request->input('message'),
//            'conversation_id' => $request->input('conversation_id'),
//            'sender_id' => $user->id,
//            'sender_type' => get_class($user),
//        ]);
//        $message = new Message($message->toArray());
        return response()->json($message);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        return view('pages.conversation.form', ['model' => $ticket]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $ticket->status = $request->status;
        $ticket->save();
        return response()->json(['status' => true, 'msg' => 'تم الحفظ بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation $conversion)
    {
        //
    }
}
