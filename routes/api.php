<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Traits\Helpers\ApiResponseTrait;

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

        Route::post('check-phone', [\App\Http\Controllers\Api\UserController::class, 'checkPhone']);

        // Register a new user with phone number
        Route::post('register', [\App\Http\Controllers\Api\UserController::class, 'register']);

        // Login user with OTP
        Route::post('login', [\App\Http\Controllers\Api\UserController::class, 'login']);

        // Generate a new OTP for login
        Route::post('generate-otp', [\App\Http\Controllers\Api\UserController::class, 'generateOTP']);

        Route::middleware('auth:user')->group(function () {
            //location
            Route::get('location', [\App\Http\Controllers\Api\LocationController::class, 'index']);
            Route::post('location', [\App\Http\Controllers\Api\LocationController::class, 'store']);

            //order
            Route::post('order', [\App\Http\Controllers\Api\OrderController::class, 'store']);
            Route::get('order', [\App\Http\Controllers\Api\OrderController::class, 'user_orders_index']); // for user
        });

    });


    Route::prefix('provider')->group(function () {
        // Register a new provider with phone number
        Route::post('register', [\App\Http\Controllers\Api\ProviderController::class, 'registerProvider']);

        // Login user with OTP
        Route::post('login', [\App\Http\Controllers\Api\ProviderController::class, 'login']);

        // Generate a new OTP for login
        Route::post('generate-otp', [\App\Http\Controllers\Api\ProviderController::class, 'generateOTP']);

        Route::post('verify', [\App\Http\Controllers\Api\ProviderController::class, 'verify']);

        Route::middleware(['auth:provider'])->group(function () {

            Route::patch('reset-password', [\App\Http\Controllers\Api\ProviderController::class, 'resetPassword']);
            //order
            Route::get('order', [\App\Http\Controllers\Api\OrderController::class, 'pending_index']); // for providers

        });
    });

    Route::get('country', [\App\Http\Controllers\Api\CountryController::class, 'country_index']);

    Route::get('city', [\App\Http\Controllers\Api\CountryController::class, 'city_index']);

    Route::get('service', [\App\Http\Controllers\Api\ServiceController::class, 'service_index']);

    Route::get('sub_service', [\App\Http\Controllers\Api\ServiceController::class, 'sub_service_index']);




    Route::get( '/loin', function () {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated or Token Expired, Please Login',
            'result' => null,
            'error_code' => 1
        ], 401);
    })->name('login');
});

