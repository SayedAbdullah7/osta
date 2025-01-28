<?php

use App\Helpers\Filter;
use App\Http\Controllers\Api\Provider\OfferController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpaceController;
use App\Models\Provider;
use App\Models\Review;
use App\Models\Service;
use App\Models\SpaceSubService;
use App\Models\SubService;
use App\Models\Warranty;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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

Route::get('/update-service', function () {
    $serviceData = [
        [
            'id' => 1,
            'category' => 'basic',
            'min_price' => 100,
            'max_price' => 500,
            'name_en' => 'Plumbing',
            'name_ar' => 'السباكة',
            'sub_services' => [
                ['name_en' => 'Pipe Installation', 'name_ar' => 'تركيب الأنابيب'],
                ['name_en' => 'Leak Repair', 'name_ar' => 'إصلاح التسربات'],
                ['name_en' => 'Drain Cleaning', 'name_ar' => 'تنظيف المصارف'],
                ['name_en' => 'Water Heater Installation', 'name_ar' => 'تركيب سخانات الماء'],
                ['name_en' => 'Faucet Repair', 'name_ar' => 'إصلاح الحنفيات'],
            ]
        ],
        [
            'id' => 2,
            'category' => 'space_based',
            'min_price' => 200,
            'max_price' => 700,
            'name_en' => 'Tiling',
            'name_ar' => 'التبليط',
            'sub_services' => [
                ['name_en' => 'Wall Tiling', 'name_ar' => 'تبليط الجدران'],
                ['name_en' => 'Floor Tiling', 'name_ar' => 'تبليط الأرضيات'],
                ['name_en' => 'Mosaic Tiling', 'name_ar' => 'تبليط الفسيفساء'],
                ['name_en' => 'Grouting', 'name_ar' => 'إغلاق الفواصل'],
                ['name_en' => 'Tile Repair', 'name_ar' => 'إصلاح البلاط'],
            ]
        ],
        [
            'id' => 3,
            'category' => 'technical',
            'min_price' => 150,
            'max_price' => 600,
            'name_en' => 'Electrical Maintenance',
            'name_ar' => 'صيانة كهربائية',
            'sub_services' => [
                ['name_en' => 'Wiring Check', 'name_ar' => 'فحص الأسلاك'],
                ['name_en' => 'Circuit Repair', 'name_ar' => 'إصلاح الدائرة'],
                ['name_en' => 'Fuse Replacement', 'name_ar' => 'استبدال الفيوز'],
                ['name_en' => 'Switch Replacement', 'name_ar' => 'استبدال المفاتيح'],
                ['name_en' => 'Socket Repair', 'name_ar' => 'إصلاح المقابس'],
            ]
        ],
        [
            'id' => 4,
            'category' => 'basic',
            'min_price' => 120,
            'max_price' => 400,
            'name_en' => 'Carpentry',
            'name_ar' => 'النجارة',
            'sub_services' => [
                ['name_en' => 'Door Repair', 'name_ar' => 'إصلاح الأبواب'],
                ['name_en' => 'Furniture Assembly', 'name_ar' => 'تركيب الأثاث'],
                ['name_en' => 'Cabinet Making', 'name_ar' => 'صناعة الخزائن'],
                ['name_en' => 'Floorboard Installation', 'name_ar' => 'تركيب الألواح الأرضية'],
                ['name_en' => 'Wooden Window Installation', 'name_ar' => 'تركيب النوافذ الخشبية'],
            ]
        ],
        [
            'id' => 5,
            'category' => 'space_based',
            'min_price' => 300,
            'max_price' => 900,
            'name_en' => 'Painting',
            'name_ar' => 'الدهانات',
            'sub_services' => [
                ['name_en' => 'Wall Painting', 'name_ar' => 'دهانات الجدران'],
                ['name_en' => 'Exterior Painting', 'name_ar' => 'دهانات الواجهة الخارجية'],
                ['name_en' => 'Ceiling Painting', 'name_ar' => 'دهانات السقف'],
                ['name_en' => 'Furniture Painting', 'name_ar' => 'دهانات الأثاث'],
                ['name_en' => 'Wallpaper Installation', 'name_ar' => 'تركيب ورق الجدران'],
            ]
        ],
        [
            'id' => 6,
            'category' => 'technical',
            'min_price' => 250,
            'max_price' => 800,
            'name_en' => 'Air Conditioner Installation',
            'name_ar' => 'تركيب مكيفات',
            'sub_services' => [
                ['name_en' => 'Split AC Installation', 'name_ar' => 'تركيب تكييف سبليت'],
                ['name_en' => 'Window AC Installation', 'name_ar' => 'تركيب تكييف نافذة'],
                ['name_en' => 'Central AC Installation', 'name_ar' => 'تركيب تكييف مركزي'],
                ['name_en' => 'AC Maintenance', 'name_ar' => 'صيانة التكييف'],
                ['name_en' => 'AC Repair', 'name_ar' => 'إصلاح التكييف'],
            ]
        ],
        [
            'id' => 7,
            'category' => 'other',
            'min_price' => 50,
            'max_price' => 250,
            'name_en' => 'Cleaning',
            'name_ar' => 'النظافة',
            'sub_services' => [
                ['name_en' => 'Office Cleaning', 'name_ar' => 'تنظيف المكاتب'],
                ['name_en' => 'House Cleaning', 'name_ar' => 'تنظيف المنازل'],
                ['name_en' => 'Carpet Cleaning', 'name_ar' => 'تنظيف السجاد'],
                ['name_en' => 'Window Cleaning', 'name_ar' => 'تنظيف النوافذ'],
                ['name_en' => 'End of Lease Cleaning', 'name_ar' => 'تنظيف نهاية العقد'],
            ]
        ],
        [
            'id' => 8,
            'category' => 'basic',
            'min_price' => 150,
            'max_price' => 450,
            'name_en' => 'Plumbing Repair',
            'name_ar' => 'إصلاح السباكة',
            'sub_services' => [
                ['name_en' => 'Pipe Replacement', 'name_ar' => 'استبدال الأنابيب'],
                ['name_en' => 'Tap Repair', 'name_ar' => 'إصلاح الصنابير'],
                ['name_en' => 'Drain Unblocking', 'name_ar' => 'فتح المجاري المسدودة'],
                ['name_en' => 'Sewer Line Repair', 'name_ar' => 'إصلاح خطوط الصرف الصحي'],
                ['name_en' => 'Water Pump Repair', 'name_ar' => 'إصلاح مضخة المياه'],
            ]
        ],
        [
            'id' => 9,
            'category' => 'space_based',
            'min_price' => 350,
            'max_price' => 850,
            'name_en' => 'Flooring',
            'name_ar' => 'الأرضيات',
            'sub_services' => [
                ['name_en' => 'Wooden Flooring', 'name_ar' => 'الأرضيات الخشبية'],
                ['name_en' => 'Ceramic Flooring', 'name_ar' => 'الأرضيات السيراميكية'],
                ['name_en' => 'Vinyl Flooring', 'name_ar' => 'الأرضيات الفينيل'],
                ['name_en' => 'Marble Flooring', 'name_ar' => 'الأرضيات الرخامية'],
                ['name_en' => 'Carpet Flooring', 'name_ar' => 'الأرضيات السجاد'],
            ]
        ],
        [
            'id' => 10,
            'category' => 'technical',
            'min_price' => 180,
            'max_price' => 500,
            'name_en' => 'Electrical Wiring',
            'name_ar' => 'الأسلاك الكهربائية',
            'sub_services' => [
                ['name_en' => 'New Wiring Installation', 'name_ar' => 'تركيب الأسلاك الجديدة'],
                ['name_en' => 'Rewiring', 'name_ar' => 'إعادة الأسلاك'],
                ['name_en' => 'Lighting Installation', 'name_ar' => 'تركيب الإضاءة'],
                ['name_en' => 'Electrical Panel Repair', 'name_ar' => 'إصلاح لوحة الكهرباء'],
                ['name_en' => 'Wiring Inspection', 'name_ar' => 'فحص الأسلاك'],
            ]
        ]
    ];
$updatedServiceIds = [];
$updateSubServiceIds = [];
    foreach ($serviceData as $data) {
        // Find the main service by its ID
//        $service = Service::find($data['id']);
        $service = Service::where('category', $data['category'])
            ->whereNotIn('id', $updatedServiceIds)
            ->first();
        if ($service) {
            // Update the main service fields
            $service->category = $data['category'];
            $service->min_price = $data['min_price'];
            $service->max_price = $data['max_price'];

            // Update translations for both English and Arabic
            $service->translateOrNew('en')->name = $data['name_en'];
            $service->translateOrNew('ar')->name = $data['name_ar'];

            // Save the main service
            $service->save();

            // Now, update the sub-services for this service (if any)
            foreach ($data['sub_services'] as $subService) {
                // Loop through each sub-service and update or create if it doesn't exist
                $existingSubService = SubService::where('service_id', $service->id)
                    ->whereNotIn('id', $updateSubServiceIds)
                    ->first();

                if ($existingSubService) {
                    // Update the existing sub-service
                    $existingSubService->translateOrNew('en')->name = $subService['name_en'];
                    $existingSubService->translateOrNew('ar')->name = $subService['name_ar'];
                    $existingSubService->save();
                    $updateSubServiceIds[] = $existingSubService->id;
                } else {
                    // Create a new sub-service if it doesn't exist
//                    SubService::create([
//                        'service_id' => $service->id,
//                        'name_en' => $subService['name_en'],
//                        'name_ar' => $subService['name_ar'],
//                    ]);
                }
            }
            $updatedServiceIds[] = $service->id;
        }
    }

    // Return success message
    return response()->json(['message' => 'Services and sub-services updated successfully!']);


});
Route::get('/update-warranty', function () {
    $warrantyData = [
        [
            'id' => 1, // Warranty with id 1
            'name_en' => 'Extended Warranty',
            'description_en' => 'Covers damages for 5 years, ensuring long-term reliability and protection.',
            'name_ar' => 'ضمان ممتد',
            'description_ar' => 'يغطي الأضرار لمدة 5 سنوات، مما يضمن الموثوقية والحماية على المدى الطويل.',
            'duration_months' => 60, // Example duration
            'percentage_cost' => 15.00, // Example cost percentage
        ],
        [
            'id' => 2, // Warranty with id 2
            'name_en' => 'Standard Warranty',
            'description_en' => 'Provides essential coverage for 1 year against accidental damages.',
            'name_ar' => 'ضمان قياسي',
            'description_ar' => 'يوفر تغطية أساسية لمدة سنة واحدة ضد الأضرار العرضية.',
            'duration_months' => 12, // Example duration
            'percentage_cost' => 10.00, // Example cost percentage
        ],
        [
            'id' => 3, // Warranty with id 3
            'name_en' => 'Premium Warranty',
            'description_en' => 'Comprehensive coverage including accidental damage protection for 3 years.',
            'name_ar' => 'ضمان مميز',
            'description_ar' => 'تغطية شاملة تشمل الحماية ضد الأضرار العرضية لمدة 3 سنوات.',
            'duration_months' => 36, // Example duration
            'percentage_cost' => 20.00, // Example cost percentage
        ]
    ];

    // Loop through each warranty data and update the corresponding warranty row
    foreach ($warrantyData as $data) {
        // Find the warranty by its ID
        $warranty = Warranty::find($data['id']);

        if ($warranty) {
            // Update the main warranty fields
            $warranty->duration_months = $data['duration_months'];
            $warranty->percentage_cost = $data['percentage_cost'];

            // Update the translation for English and Arabic
            $warranty->translateOrNew('en')->name = $data['name_en'];
            $warranty->translateOrNew('en')->description = $data['description_en'];

            $warranty->translateOrNew('ar')->name = $data['name_ar'];
            $warranty->translateOrNew('ar')->description = $data['description_ar'];

            // Save the warranty (this will save both the main model and its translations)
            $warranty->save();
        }
    }

});
Route::get('/update-token-expiration', function () {
    // Fetch all rows from the personal_access_tokens table
    $tokens = DB::table('personal_access_tokens')->get();

    // Loop through all tokens and set expires_at to now
    foreach ($tokens as $token) {
        DB::table('personal_access_tokens')
            ->where('id', $token->id) // Identify each token by its ID
            ->update(['expires_at' =>Carbon::now()]); // Set expires_at to now
    }

    return response()->json(['message' => 'Token expiration updated for all tokens']);
});
Route::get('/code',function (){
    return Review::find(14);

    $order = \App\Models\Order::find(580);
    return $order->calculatePrice();
    return $order;
    return    $purchases= $order->orderDetails()->purchases()->first();

    return $order;
    $spaceId = 11; // Your space_id
    $subServiceId = 58; // Your sub_service_id
    $maxPrice = '135.00'; // Your new max_price

    // Fetch the space_sub_service record
    $spaceSubService = SpaceSubService::where('space_id', $spaceId)
        ->where('sub_service_id', $subServiceId)
        ->first();

    // Check if the record was found
    if ($spaceSubService) {
        $spaceSubService->max_price = $maxPrice;
        $spaceSubService->save();
        return $spaceSubService;
    } else {
        return response()->json(['message' => 'Record not found'], 404);
    }
});
//Route::resource('area', \App\Http\Controllers\AreaController::class);
Route::post('/upload-image', [\App\Http\Controllers\ServiceController::class,'uploadImage'])->name('upload-image');
    Route::get('/update', [\App\Http\Controllers\SchemaUpdateController::class, 'updateSchema']);

Route::resource('area', \App\Http\Controllers\AreaController::class);
Route::resource('service', \App\Http\Controllers\ServiceController::class);
Route::resource('sub-service', \App\Http\Controllers\SubServiceController::class);
Route::resource('discount-code', \App\Http\Controllers\DiscountCodeController::class);
Route::resource('user', \App\Http\Controllers\UserController::class);
Route::resource('review', \App\Http\Controllers\ReviewController::class);
Route::resource('provider', \App\Http\Controllers\ProviderController::class);
Route::resource('order', \App\Http\Controllers\OrderController::class);
Route::resource('ticket', \App\Http\Controllers\ConversationController::class);
//Route::get('/ticket', [ConversationController::class, 'index']);
Route::get('/datatable-data', [OrderController::class, 'getData'])->name('datatable.data');
Route::get('/chat/conversation/{conversationId}', [\App\Http\Controllers\ConversationController::class, 'getConversation']);
Route::get('/chat/messages/{conversationId}', [\App\Http\Controllers\ConversationController::class, 'getMessages']);
Route::post('/chat/send', [\App\Http\Controllers\ConversationController::class, 'sendMessage']);

//Route::resource('space', \App\Http\Controllers\SpaceController::class);
// Custom routes for space_sub_service
Route::prefix('space')->group(function () {
    Route::get('/', [SpaceController::class, 'index'])->name('space_sub_service.index');
    Route::get('/create', [SpaceController::class, 'create'])->name('space_sub_service.create');
    Route::post('/', [SpaceController::class, 'store'])->name('space_sub_service.store');
    Route::get('/{space_id}/{sub_service_id}/edit', [SpaceController::class, 'edit'])->name('space_sub_service.edit');
    Route::put('/{space_id}/{sub_service_id}', [SpaceController::class, 'update'])->name('space_sub_service.update');
    Route::delete('/{space_id}/{sub_service_id}', [SpaceController::class, 'destroy'])->name('space_sub_service.destroy');
});

Route::resource('category', \App\Http\Controllers\CategoryController::class);
Route::resource('section', \App\Http\Controllers\SectionController::class);
Route::resource('company', \App\Http\Controllers\CompanyController::class);
Route::resource('product', \App\Http\Controllers\ProductController::class);
Route::resource('conversation', \App\Http\Controllers\ConversationController::class);
Route::resource('warranty', \App\Http\Controllers\WarrantyController::class);
Route::resource('level', \App\Http\Controllers\LevelController::class);
Route::resource('wallet', \App\Http\Controllers\WalletController::class);
Route::resource('transaction', \App\Http\Controllers\TransactionController::class)->except('show');
Route::get('transaction/{walletId}', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transaction.wallet.index');
Route::resource('invoice', \App\Http\Controllers\InvoiceController::class)->except('show');
Route::get('invoice/{orderId}', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoice.order.index');
Route::resource('payment', \App\Http\Controllers\PaymentController::class);
Route::resource('setting', \App\Http\Controllers\SettingController::class);

Route::resource('contractor-request', \App\Http\Controllers\ContractorRequestController::class);
Route::get('seed_setting', [\App\Http\Controllers\SettingController::class, 'seed']);

Route::get('/privacy-policy', function () {
    \Debugbar::disable();
    return view('privacy-policy');
});
Route::get('/test', function () {
     $service = \App\Models\Service::find(26);
    return $service;
});
Route::get('/user1', function () {
    if (!Auth::check()) {
         Auth::loginUsingId(1); // Assuming admin ID 1 is a valid admin
    }
    return Auth::user();
});
Route::get('/user2', function () {
    if (!Auth::check()) {
         Auth::loginUsingId(2); // Assuming admin ID 1 is a valid admin
    }
    return Auth::user();
});
Route::get('/push', function () {

//    $user = User::find($request->input('user_id')); // Target user
    $title = request()->input('title', 'Notification Title'); // Notification title
    $body = request()->input('body','Notification Body'); // Notification body        // Send push notification
    $firebaseService = new FirebaseNotificationService();
    $user = \App\Models\DeviceToken::latest();
    return $firebaseService->sendPushNotificationSync($user, $title, $body);
    return response()->json(['message' => 'Notification sent successfully.']);
});
Route::get('/test-send-offer', function () {
    // Hardcoded test data
    $payload = [
        'price' => '0',
        'order_id' => '769',
        'time' => 'now',
        'latitude' => '1.082413',
        'longitude' => '1.913574',
        'provider_id' => 3,
    ];
//    $request = Illuminate\Http\Reques
$request = new Illuminate\Http\Request();
    $request->merge($payload);
    $controller = app(\App\Services\ProviderOfferService::class);
    return $controller->sendOfferFromProvider($payload);
    // Simulate authenticated provider with ID 3
//    $provider = Provider::find(3);
//
//    // Create a Sanctum token for the provider
//    $token = $provider->createToken('TestToken')->plainTextToken;

    // Create a request object with the hardcoded data
//    $request = \Illuminate\Http\Request::create('/api/send-offer', 'POST', $payload);

    // Add the token to the Authorization header
//    $request->headers->set('Authorization', 'Bearer ' . $token);

    // Simulate the controller's sendOffer method

    // Hardcoded test data
    $payload = [
        'price' => '0',
        'order_id' => '769',
        'time' => 'now',
        'latitude' => '1.082413',
        'longitude' => '1.913574',
    ];

    // Simulate authenticated provider with ID 3
    $provider = Provider::find(3);
    Auth::guard('provider')->login($provider);  // Log in the provider using the 'provider' guard

    // Create a request object with the hardcoded data
    $request = \Illuminate\Http\Request::create('/api/send-offer', 'POST', $payload);

    // Simulate the controller's sendOffer method
    $controller = new OfferController();
    return $controller->sendOffer($request);
});
Route::get('/auth', function () {
//    Auth::guard('web')->logout();
//    Auth::logout();
////    if (!Auth::guard('admin')->check()) {
//            Auth::loginUsingId(1); // Assuming admin ID 1 is a valid admin
//        return \auth()->user();
////    }
//    return 'done';
//     $user  = \App\Models\User::first();
//    return $user->deviceTokens()->first()->update(['is_set_notification',1]);
//    DB::transaction(function () {
//        // Step 1: Identify duplicate tokens and keep the latest record
//        $duplicates = DB::table('device_tokens')
//            ->select('token', DB::raw('MAX(id) as last_id'))
//            ->groupBy('token')
//            ->havingRaw('COUNT(token) > 1')
//            ->get();
//
//        // Delete duplicate records, keeping the latest one
//        foreach ($duplicates as $duplicate) {
//            DB::table('device_tokens')
//                ->where('token', $duplicate->token)
//                ->where('id', '!=', $duplicate->last_id)
//                ->delete();
//        }
//
//        // Step 2: Ensure no user or provider has more than one token
//        $userDuplicates = DB::table('device_tokens')
//            ->select('user_id', DB::raw('MAX(id) as last_id'))
//            ->groupBy('user_id')
//            ->havingRaw('COUNT(user_id) > 1')
//            ->get();
//
//        foreach ($userDuplicates as $duplicate) {
//            DB::table('device_tokens')
//                ->where('user_id', $duplicate->user_id)
//                ->where('id', '!=', $duplicate->last_id)
//                ->delete();
//        }
//
//        $providerDuplicates = DB::table('device_tokens')
//            ->select('provider_id', DB::raw('MAX(id) as last_id'))
//            ->groupBy('provider_id')
//            ->havingRaw('COUNT(provider_id) > 1')
//            ->get();
//
//        foreach ($providerDuplicates as $duplicate) {
//            DB::table('device_tokens')
//                ->where('provider_id', $duplicate->provider_id)
//                ->where('id', '!=', $duplicate->last_id)
//                ->delete();
//        }
//    });

    $firebaseService = new FirebaseNotificationService();
    return $firebaseService->sendNotificationToUser([54],[46],'hello','this is a test');
    return   \App\Enums\OrderCategoryEnum::array();
    if (!Auth::check()) {
        return Auth::loginUsingId(1); // Assuming admin ID 1 is a valid admin
    }
    return \Carbon\Carbon::now()->toDateString();
    return [
        'gender' => Filter::select('Select Gender', [
            '1' => 'Male',
            '0' => 'Female',
        ]),

    ];


    return Auth()->user();
    if (!Auth::guard('admin')->check()) {
        Auth::guard('admin')->loginUsingId(1); // Assuming admin ID 1 is a valid admin
    }    $user = \App\Models\Admin::find(1);
    return Auth('admin')->user();

})->name('stadium-owner.index');
//Route::get('/test2', function () {
//})->name('user.index');
Route::get('/users', [\App\Http\Controllers\SchemaUpdateController::class, 'users'])->name('profile.edit');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
