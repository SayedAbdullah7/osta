<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\TicketResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Services\MessageService;
use Illuminate\Http\Request;
use App\Models\Ticket;
class TicketController extends Controller
{
    use ApiResponseTrait;
    private $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = 10;
        $page = request()->input('page', 1);
        $tickets = Ticket::with('conversation')->orderByDesc('id')->simplePaginate($perPage , ['*'], 'page', $page);
        return $this->respondWithResource(TicketResource::collection($tickets),'');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ticket = new Ticket();
        $ticket->title = $request->title;
        $ticket->description = $request->description;
        $ticket->user_id = $request->user()->id;
        $ticket->user_type = \get_class($request->user());
        $ticket->save();
        $conversation = $this->messageService->createConversationForModel($ticket,[$request->user()],$request->description);
        $ticket->load('conversation');
        return $this->respondWithResource(new TicketResource($ticket), 'Ticket created successfully');
        return $this->apiResponse(
            [
                'success' => true,
                'result' => [
                    'conversation' => $conversation,
                    'ticket' => new TicketResource($ticket),
                ],
                'message' => 'Messages retrieved successfully'
            ]
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
