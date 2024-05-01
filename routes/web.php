<?php

use App\Services\SocketService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/open-chat', function () {
    $order = \App\Models\Order::first();

    if (!$order->conversation) {
        $conversation = new \App\Models\Conversation();
        $conversation->name = "order #".$order->id;
        $conversation->is_active = 1;
        $conversation->type = 'order';
        $conversation->model_id = $order->id;
        $conversation->model_type = get_class($order);
        $conversation->save();

        if(!$order->provider_id ){
            $order->provider_id = \App\Models\Provider::first()->id;
            $order->save();
        }

        $conversationMember = new \App\Models\ConversationMember();
        $conversationMember->conversation_id = $conversation->id;
        $conversationMember->user_id = $order->user_id;
        $conversationMember->user_type = get_class($order->user);
        $conversationMember->save();

        $conversationMember = new \App\Models\ConversationMember();
        $conversationMember->conversation_id = $conversation->id;
        $conversationMember->user_id = $order->provider_id;
        $conversationMember->user_type = get_class($order->provider);
        $conversationMember->save();
        return $conversation;
    }

});

Route::get('/clear', function () {
        return Artisan::call('optimize:clear');
});
Route::get('/test', function () {
    $providers = \App\Models\Provider::with('media')->withCount(['orders'=>function($query){
        $query->where('status',\App\Enums\OrderStatusEnum::DONE);
    }])->get();
//    return $providers;
    return \App\Http\Resources\ProviderResource::collection($providers);
    \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::logout() : \Illuminate\Support\Facades\Auth::login(\App\Models\User::first());
    return auth()->user();
    return \App\Enums\OrderCategoryEnum::cases();

    return App\Services\NotificationService::sendNotification('test', 'test', 'test');
    return view('welcome');
});

