<?php

use App\Http\Controllers\AccountDeletionRequestController;
use App\Http\Controllers\Api\ProviderLocationController;
use App\Http\Controllers\Api\User\DiscountCodeController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\GeneralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware([])->group(function () {

    Route::prefix('user')->group(function () {


        Route::post('check-phone', [\App\Http\Controllers\Api\UserController::class, 'checkPhoneRegistered']);

        // Register a new user with phone number
        Route::post('register', [\App\Http\Controllers\Api\UserController::class, 'register']);

        // Login user with OTP
        Route::post('login', [\App\Http\Controllers\Api\UserController::class, 'login']);

        // Generate a new OTP for login
        Route::post('generate-otp', [\App\Http\Controllers\Api\UserController::class, 'generateOTP']);

        Route::middleware('auth:user')->group(function () {
            Route::post('contractor-requests', [\App\Http\Controllers\Api\ContractorRequestController::class, 'store']);

            Route::get('profile', [\App\Http\Controllers\Api\UserController::class, 'profile']);
            Route::post('update-profile', [\App\Http\Controllers\Api\UserController::class, 'update']);
            Route::post('profile', [\App\Http\Controllers\Api\UserController::class, 'update']);
            Route::put('profile', [\App\Http\Controllers\Api\UserController::class, 'update']);

            //location
            Route::get('location', [\App\Http\Controllers\Api\LocationController::class, 'index']);
            Route::post('location', [\App\Http\Controllers\Api\LocationController::class, 'store']);
            Route::delete('location/{id}', [\App\Http\Controllers\Api\LocationController::class, 'destroy']);

            //order
            Route::post('order', [\App\Http\Controllers\Api\User\OrderController::class, 'store']);
            Route::get('order', [\App\Http\Controllers\Api\User\OrderController::class, 'getUserOrders']); // for user
            Route::get('order/{order}', [\App\Http\Controllers\Api\User\OrderController::class, 'getUserOrder']); // show
//            Route::get('order', [\App\Http\Controllers\Api\User\OrderController::class, 'user_orders_index']); // for user

            Route::get('offer', [\App\Http\Controllers\Api\User\OfferController::class, 'all']); // all offers
            Route::get('order/{order}/offer', [\App\Http\Controllers\Api\User\OfferController::class, 'index']); // my orders for

            Route::get('offer/{orderId}', [\App\Http\Controllers\Api\User\OfferController::class, 'index']);
            Route::post('offer/{offerId}/accept', [\App\Http\Controllers\Api\User\OfferController::class, 'acceptOffer']);
            Route::post('offer/{offerId}/reject', [\App\Http\Controllers\Api\User\OfferController::class, 'rejectOffer']);

            Route::post('/discount-codes/check-validity', [DiscountCodeController::class, 'checkValidity']);


            Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
            Route::post('/skip-reviews', [\App\Http\Controllers\Api\ReviewController::class, 'skip']);
            Route::get('/providers/{providerId}/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'getProviderReviews']);
            Route::get('/my-reviews', [\App\Http\Controllers\Api\ReviewController::class, 'myReviews']);

            Route::get('banners', [\App\Http\Controllers\Api\UserController::class, 'banners']);
            Route::get('home', [\App\Http\Controllers\Api\UserController::class, 'home']);
            Route::post('setting', [\App\Http\Controllers\Api\UserController::class, 'setNotificationSetting']);

            Route::get('warranties', [\App\Http\Controllers\Api\WarrantyController::class, 'index']);

            Route::post('order/{orderId}/make-done/', [\App\Http\Controllers\Api\Provider\OrderController::class, 'updateOrderToDone']); // send offer for order

            Route::post('order/{orderId}/pay', [\App\Http\Controllers\Api\InvoiceController::class, 'pay']);

//            Route::patch('order/{orderId}/remove', [\App\Http\Controllers\Api\Provider\OrderController::class, 'remove']); // my orders for providers
            Route::patch('order/{orderId}/cancel', [\App\Http\Controllers\Api\User\OrderController::class, 'cancelOrder']); // my orders for providers

            Route::delete('delete', [AccountDeletionRequestController::class, 'destroy']);

        });

    });


    Route::prefix('provider')->group(function () {

        Route::post('check-phone', [\App\Http\Controllers\Api\ProviderController::class, 'checkPhoneRegistered']);

        // Register a new provider with phone number
        Route::post('register', [\App\Http\Controllers\Api\ProviderController::class, 'registerProvider']);

        // Login user with OTP
        Route::post('login', [\App\Http\Controllers\Api\ProviderController::class, 'login']);

        // Generate a new OTP for login
        Route::post('generate-otp', [\App\Http\Controllers\Api\ProviderController::class, 'generateOTP']);

        Route::post('verify', [\App\Http\Controllers\Api\ProviderController::class, 'verify']);
        Route::delete('delete', [AccountDeletionRequestController::class, 'destroy'])->middleware('auth:provider');

        Route::middleware(['auth:provider','approved','check-wallet-balance'])->group(function () {
            Route::get('subscription/available', [\App\Http\Controllers\Api\SubscriptionController::class, 'getLastActiveSubscription']);
            Route::post('subscription/{subscription}', [\App\Http\Controllers\Api\SubscriptionController::class, 'renewSubscription']);
            Route::get('subscription', [\App\Http\Controllers\Api\SubscriptionController::class, 'getCurrentSubscription']);

            Route::post('/live-location', [ProviderLocationController::class, 'store']);


            Route::get('profile', [\App\Http\Controllers\Api\ProviderController::class, 'profile']);
            Route::put('profile', [\App\Http\Controllers\Api\ProviderController::class, 'update']);
            Route::post('profile', [\App\Http\Controllers\Api\ProviderController::class, 'update']);

            Route::patch('reset-password', [\App\Http\Controllers\Api\ProviderController::class, 'resetPassword']);


            Route::get('home', [\App\Http\Controllers\Api\ProviderController::class, 'home']);

            //order
            Route::get('order', [\App\Http\Controllers\Api\Provider\OrderController::class, 'getPendingOrders']); // for providers

            Route::get('my-orders', [\App\Http\Controllers\Api\Provider\OrderController::class, 'getProviderOrders']); // my orders for providers
            Route::get('order/{order}/details', [\App\Http\Controllers\Api\Provider\OrderController::class, 'getOrderDetails']); // my orders for providers

//            Route::patch('order/{order}/accept', [\App\Http\Controllers\Api\Provider\OrderController::class, 'accept']); // my orders for providers

            Route::patch('order/{orderId}/remove', [\App\Http\Controllers\Api\Provider\OrderController::class, 'remove']); // my orders for providers

            Route::get('my-offers', [\App\Http\Controllers\Api\Provider\OfferController::class, 'index']); // my orders for providers

            Route::post('send-offer', [\App\Http\Controllers\Api\Provider\OfferController::class, 'sendOffer']); // send offer for order

            Route::post('order/{orderId}/make-done/', [\App\Http\Controllers\Api\Provider\OrderController::class, 'updateOrderToDone']); // send offer for order
//            Route::post('order/{orderId}/make-order-done/', [\App\Http\Controllers\Api\Provider\OrderController::class, 'updateOrderToDone']); // send offer for order
            Route::get('/levels', [\App\Http\Controllers\Api\ProviderStatisticController::class, 'level_index']);

            Route::get('banners', [\App\Http\Controllers\Api\UserController::class, 'banners']);

            Route::post('setting', [\App\Http\Controllers\Api\UserController::class, 'setNotificationSetting']);

            Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
            Route::get('/users/{providerId}/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'getUserReviews']);
            Route::get('/my-reviews', [\App\Http\Controllers\Api\ReviewController::class, 'myReviews']);


        });



        Route::get('/loin', function () {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated or Token Expired, Please Login',
                'result' => null,
                'error_code' => 1
            ], 401);
        })->name('login');

        //routes for test
        //aprove authed ptovider
        Route::get('/approve', function () {
            auth()->guard('provider')->user()->update(['is_approved' => true]);
            return response()->json(['message' => 'done'], 200);
        });
        Route::get('disapprove', function () {
            auth()->guard('provider')->user()->update(['is_approved' => false]);
//            return     \App\Models\Provider::where('id', auth()->guard('provider')->user()->id)->update(['is_approved' => false]);
            return response()->json(['message' => 'done'], 200);
        });
    });

    Route::get('space', [\App\Http\Controllers\Api\SpaceController::class, 'index']);

    Route::get('country', [\App\Http\Controllers\Api\CountryController::class, 'country_index']);

    Route::get('city', [\App\Http\Controllers\Api\CountryController::class, 'city_index']);

    Route::get('service', [\App\Http\Controllers\Api\ServiceController::class, 'service_index']);

    Route::get('sub_service', [\App\Http\Controllers\Api\ServiceController::class, 'sub_service_index']);
    Route::get('setting', [\App\Http\Controllers\Api\ServiceController::class, 'getSetting']);

    //chat
    Route::prefix('')->middleware(['auth:sanctum','approved'])->group(function () {
//        Route::get('/wallet/balance', [WalletController::class, 'balance']);

        // write send-message route here
        Route::get('inbox', [\App\Http\Controllers\Api\ConversationController::class, 'index']);
        Route::post('message', [\App\Http\Controllers\Api\MessageController::class, 'sendMessage'])->name('send.message');
        Route::get('message', [\App\Http\Controllers\Api\MessageController::class, 'index'])->name('get.message');
        Route::post('message/request-action', [\App\Http\Controllers\Api\MessageController::class, 'makeAction'])->name('make.action');
        Route::post('message/response-action', [\App\Http\Controllers\Api\MessageController::class, 'responseAction'])->name('response.action');

        Route::get('faq', [\App\Http\Controllers\Api\FaqController::class, 'index']);
        Route::get('faq-category', [\App\Http\Controllers\Api\FaqController::class, 'categories']);


        Route::get('/wallet/', [WalletController::class, 'show']);
        Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
        Route::get('/invoice/{orderId}', [\App\Http\Controllers\Api\InvoiceController::class, 'show']);

        Route::post('/invoice/{orderId}/additional-cost', [\App\Http\Controllers\Api\InvoiceController::class, 'updateAdditionalCost']);

        Route::get('/ticket', [\App\Http\Controllers\Api\TicketController::class, 'index']);
        Route::post('/ticket', [\App\Http\Controllers\Api\TicketController::class, 'store']);

        Route::get('/ticket/{ticket}', [\App\Http\Controllers\Api\TicketController::class, 'show']);
        Route::post('/ticket/{ticket}/message', [\App\Http\Controllers\Api\TicketController::class, 'storeMessage']);

        Route::get('/social-media', [GeneralController::class, 'getSocialMediaLinks']);

    });


    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/wallet/deposit', [WalletController::class, 'deposit']);
        Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
//        Route::post('/wallet/withdraw', [WalletController::class, 'withdraw']);
        Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
    });


});
