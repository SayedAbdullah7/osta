<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ConverstionResource;
use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Conversation;
use App\Services\MessageService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    use ApiResponseTrait;

    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conversations = $this->messageService->geAvailableOrderConversationsListWithTheOtherMember();
        return ConverstionResource::collection($conversations);
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
    public function edit(Conversation $conversion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Conversation $conversion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Conversation $conversion)
    {
        //
    }
}
