<?php

use App\Enums\OrderCategoryEnum;
use App\Http\Controllers\SchemaUpdateController;
use App\Models\Service;
use Database\Seeders\ProviderStatisticSeeder;
use App\Enums\OrderStatusEnum;
use App\Http\Resources\MessageResource;
use App\Models\Provider;
use App\Models\ProviderStatistic;
use App\Services\ProviderStatisticService;
use App\Services\SocketService;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

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

Route::get('/update-schema', [SchemaUpdateController::class, 'updateSchema']);

Route::get('set-services-images', function () {
    $services = Service::all(); // Get all services

    // Directory where images are stored
    $directory = public_path('app/services');

    // Check if directory exists
    if (!File::isDirectory($directory)) {
        return 'Directory not found.';
    }

    // Get all files in the directory
    $files = array_diff(scandir($directory), ['.', '..']);

    // Check if there are any files
    if (empty($files)) {
        return 'No files found in the directory.';
    }

    foreach ($services as $service) {
        // Choose a random file from the list
        $randomFile = $files[array_rand($files)];
        $filePath = $directory . '/' . $randomFile;

        // Check if the file exists before adding it
        if (!File::exists($filePath)) {
            continue;
        }

        // Check if the service already has a media item
        if ($service->hasMedia('images')) {
            // Delete the existing media items in the 'images' collection
            $service->clearMediaCollection('images');
        }

        // Add the new image to the service
        $service->addMedia($filePath)->toMediaCollection('images');
    }

    return 'Images added to services successfully!';
});


Route::get('test', function () {
    $service_id = 2;
    $service = Service::find($service_id);
//    return $service->category == OrderCategoryEnum::Other->;
    $loadSpaces = false;
    if ($service && in_array($service->category, [OrderCategoryEnum::SpaceBased, OrderCategoryEnum::Other], true)) {
        $loadSpaces = true;
    }
    return $loadSpaces;
    return \App\Models\Order::count();
    return \App\Models\Offer::count();
    $order = \App\Models\Order::with('subServices')->find('332');
    return $order;
//    $order = \App\Models\Order::first();
    $service_id = $order->service_id;
    $lastHour = Carbon::now()->subHours(24)->toDateTimeString();
//    $providers = \App\Models\Provider::whereHas('tokens', static function ($q) use ($lastHour) {
//        $q->where('`personal_access_tokens.last_used_at', '>=', $lastHour);
//    })->whereHas('services',function ($q2) use ($service_id) {
//        $q2->where('id',$service_id);
//    })->pluck('id')->toArray();
//    return $providers;
//    DB::listen(function ($query) {
//        Log::info($query->sql);
//        Log::info($query->bindings);
//        Log::info($query->time);
//    });
//    return Provider::pluck('id')->toArray();
    $lastHour = Carbon::now()->subHours(24)->toDateTimeString();
    return $provider = \App\Models\Provider::whereHas('tokens', function ($q) use ($lastHour) {
        $q->where('last_used_at', '>=', $lastHour);
    })
        ->whereHas('services', function ($q2) use ($service_id) {
            $q2->where('services.id', $service_id);
        })
        ->pluck('id')->toArray();
    $orderService = app(\App\Services\UserOrderService::class);
    return $orderService->pushToSocket(\App\Models\Order::first());
    $socketService = new SocketService();
    $data = ['key' => 'value']; // Replace with your actual data

    $users = [1, 2, 3]; // Replace with your actual user IDs
    $event = 'testEvent'; // Replace with your actual event
    $msg = 'testMessage'; // Replace with your actual message
    return $response = $socketService->push('provider', $data, $users, $event, $msg);

    return response()->json($response);
    $socketService = app(\App\Services\SocketService::class);
});
Route::get('/privacy-policy', function () {
    \Debugbar::disable();
    return view('privacy-policy');
});
Route::get('/clear', function () {
    return Artisan::call('optimize:clear');
});

Route::get('/command', function () {
    // artisan
    Artisan::call('optimize:clear');
    Artisan::call('optimize');
    Artisan::call('view:cache');
//    Artisan::call('icons:cache');

//    Artisan::call('icons:cache');
    // artisan
    return 'optimize and icons and cahe';
});
Route::get('/approve-all', function () {
    \App\Models\Provider::query()->update(['is_approved' => 1]);
    return 'done';
});

Route::get('/open-chat', function () {
    return Artisan::call('storage:link');

    $order = \App\Models\Order::find(1);
    return (new \App\Services\UserOfferService())->createConversationForOrder($order);

    if (!$order->conversation) {
        $conversation = new \App\Models\Conversation();
        $conversation->name = "order #" . $order->id;
        $conversation->is_active = 1;
        $conversation->type = 'order';
        $conversation->model_id = $order->id;
        $conversation->model_type = get_class($order);
        $conversation->save();

        if (!$order->provider_id) {
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
Route::get('/make-order-done', function () {
    $order = \App\Models\Order::whereNotNull('provider_id')->whereNotNull('price')->whereNot('status', \App\Enums\OrderStatusEnum::DONE)->first();
    if (!$order) {
        return 'no order';
    }
    return (new \App\Services\WalletService())->payByWallet($order);
    return app(\App\Services\WalletService::class)->payCash($order);
    return $order;
});
Route::get('/', function () {
    $user = \App\Models\User::first();
    $offers = \App\Models\Offer::whereHas('order', function ($query) use ($user) {
        $query->where('user_id', $user->id)->pending();
    })->get();
    return $offers;
    $offers = $user->orders()->pending()->whereHas('offers', function ($query) {
        $query->where('status', OrderStatusEnum::PENDING);
    })->with('offers')->get();
    return $offers;
    where('offers.status', OrderStatusEnum::PENDING)->get();
    return $offers;
    $offers = \App\Models\Offer::orderBy('id', 'desc')->get();

    return $offers;
    $messages = \App\Models\Message::with('media')->get();
    return MessageResource::collection($messages);

    return $messages;

    return $providers = \App\Models\Provider::with('media')->withCount(['orders' => function ($query) {
        $query->where('status', \App\Enums\OrderStatusEnum::DONE);
    }])->get();
    return '';
    $user = \App\Models\User::first();
    $provider = \App\Models\Provider::first();


    return

        $conversation = \App\Models\Conversation::first();
//    return $conversation->members;
    $socketService = new SocketService();
    $data = ['key' => 'value']; // Replace with your actual data

    $users = [1, 2, 3]; // Replace with your actual user IDs
    $event = 'testEvent'; // Replace with your actual event
    $msg = 'testMessage'; // Replace with your actual message
    return $response = $socketService->push('provider', $data, $users, $event, $msg);

    return response()->json($response);
    $socketService = app(\App\Services\SocketService::class);


//    $response = $socketService->push($data, $users, $event, $msg);

//    return response()->json($response);
//    });
//    return (new \App\Services\SocketService)->push(['test'], [1, 2], 'test', 'test');


    $response = Http::post('https://socket.marathon.best/emit',
        [
            'room' => 'your_room',
            'to' => 'your_target',
            'data' => 'your_data',
        ]
    );

// You can check the response status and body like this:
    if ($response->successful()) {
        return $response->body();
    }
    return $response;
    return 'done';
    $s = 's';
//    return $s;
    return phpinfo();
    return Artisan::call('storage:link');
    return \App\Models\Order::where('status', \App\Enums\OrderStatusEnum::PENDING)->get();
    $order = \App\Models\Order::first();
    return $order->status == \App\Enums\OrderStatusEnum::PENDING;
    return view('welcome');
});

Route::get('/clear', function () {
    return Artisan::call('optimize:clear');
});
Route::get('/test2', function () {
    $subServices = ['1', '2', '3'];
    $quantities = ['100', '200', '300'];

    $data = array_combine($subServices, $quantities);

    $pivotData = [];
    foreach ($data as $subServiceId => $quantity) {
        $pivotData[$subServiceId] = ['quantity' => $quantity];
    }
    $order = \App\Models\Order::with('subServices')->first();
    $order->subServices()->attach($pivotData);
    return $order;

    return $providers = \App\Models\Provider::with('media')->withCount(['orders' => function ($query) {
        $query->where('status', \App\Enums\OrderStatusEnum::DONE);
    }])->get();
//    return $providers;
    return \App\Http\Resources\ProviderResource::collection($providers);
    \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::logout() : \Illuminate\Support\Facades\Auth::login(\App\Models\User::first());
    return auth()->user();
    return \App\Enums\OrderCategoryEnum::cases();

    return App\Services\NotificationService::sendNotification('test', 'test', 'test');
    return view('welcome');
});

Route::get('/create-provider-statistics', function () {
    return Artisan::call('db:seed', [
        '--class' => ProviderStatisticSeeder::class
    ]);
    $faker = Faker::create();
    $providerStatisticService = app(ProviderStatisticService::class);

    $maxAttempts = 10; // Set a maximum number of attempts to create ProviderStatistics
    $loopCount = 0;    // Initialize a counter for loop iterations

    for ($i = 0; $i < $maxAttempts; $i++) {
        $provider = Provider::doesntHave('currentMonthStatistic')->inRandomOrder()->first();

        // If no provider is available, break the loop
        if (!$provider) {
            // Optionally, log or handle the scenario where no provider is available
            break;
        }

        // Create a ProviderStatistic entry
        $providerStatistic = ProviderStatistic::create([
            'provider_id' => $provider->id,
            'month' => Carbon::now()->startOfMonth(),
            'orders_done_count' => $faker->numberBetween(0, 40),
            'level' => 1,
            'orders_remaining_for_next_level' => 0,
        ]);

        // Recalculate provider level
        $providerStatisticService->recalculateProviderLevel($provider->id);

        $loopCount++; // Increment the loop counter
    }

    // Optionally, you can handle the result of the loop count here, e.g., logging it
    // Log::info("ProviderStatisticSeeder executed $loopCount times before stopping.");
});

