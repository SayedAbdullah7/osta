<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\MediaResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Location;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserAction;
use App\Services\AccountDeletionService;
use App\Services\FirebaseNotificationService;
use App\Services\OTPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class UserController
 * @package App\Http\Controllers\Api
 */
class UserController extends Controller
{
    use ApiResponseTrait;

    /**
     * Check if a phone number is registered.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function checkPhoneRegistered(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        // Check if the user is verified and has the provided phone number
        $user = User::verified()->where('phone', $request->phone)->first();

        // If the user does not exist, respond with a success message
        if (!$user) {
//            return $this->respondSuccess('complete register process');
            return $this->respondSuccessWithData('complete register process',false );
        }

        // Generate an OTP for the user
        $otpService = new OTPService();
        $otpService->generateOTP($user);
//        return $this->respondSuccess('OTP send');
//        return $this->respondSuccess('test');
        return $this->respondSuccessWithData('OTP send',true );
    }

    /**
     * Register a new user with phone number.
     *
     * @param RegisterUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserRequest $request): \Illuminate\Http\JsonResponse
    {
        // Delete any unverified users with the same phone number
        User::where('phone', $request->phone)->notVerified()->delete();

        // Create a new user and send them an OTP
        $user = DB::transaction(function () use ($request) {

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->country_id = $request->country_id;
            $user->gender = $request->gender == 'male' ? 1 : 0;
            $user->date_of_birth = $request->date_of_birth;

            $user->save();

            // If the request has an image, add it to the user's profile
            if ($request->hasFile('personal')) {
                $user->addMediaFromRequest('personal')->toMediaCollection('personal');
            }

            // Send verification code via SMS
            $otpService = new OTPService();
            $otpService->generateOTP($user);
            return $user;
        });
        return $this->respondWithResource(new UserResource($user), 'registered successfully, and otp sent');
    }

    /**
     * Login user with OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'otp' => 'required|string',
            'firebase_token' => 'required|string', // Firebase token is required
            'is_ios' => 'required|boolean', // Determine if the device is iOS
        ]);

        // Find the user
        $user = $this->getUserByPhone($request->phone);
        if (!$user) {
            return $this->respondNotFound('User not found');
        }

        // Check if the OTP is valid
        if (!OTPService::verifyOTP($user, $request->otp)) {
            return $this->respondError('Invalid OTP', 401);
        }

        // If the user is not verified, verify them
        if (!$user->isVerified()) {
            $user->changeToVerify();
            $user->save();
        }

        // Clear the OTP after successful login
        OTPService::destroyOTPs($user);

        // Handle Firebase token
        DB::transaction(function () use ($user, $request) {
            // Delete duplicate tokens
            \App\Models\DeviceToken::where('token', $request->firebase_token)->delete();
//            \App\Models\DeviceToken::where('token', $request->firebase_token)->orWhere('user_id', $user->id)->delete();

            // Save the new token
            \App\Models\DeviceToken::create([
                'user_id' => $user->id,
                'provider_id' => null, // Ensure this is null for users
                'token' => $request->firebase_token,
                'is_ios' => $request->input('is_ios', false),
            ]);
        });
        AccountDeletionService::cancelDeletionRequest($user);

        // Create a new token for the user
        $token = $user->createToken('app-token')->plainTextToken;
        $user->token = $token;
        AccountDeletionService::cancelDeletionRequest($user);

        return $this->respondWithResource(new UserResource($user), 'login successfully', 200);
    }

    /**
     * Generate a new OTP for login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOTP(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        // Find the user
        $user = $this->getUserByPhone($request->phone);

        // If the user does not exist, respond with an error
        if (!$user) {
            return $this->respondNotFound('User not found');
        }

        // Generate an OTP for the user
        $otpService = new OTPService();
        $code = $otpService->generateOTP($user);
        return $this->respondSuccess('OTP sent');
    }

    /**
     * Get a user by their phone number.
     *
     * @param $phone
     * @return mixed
     */
    protected function getUserByPhone($phone)
    {
        return User::where('phone', $phone)->first();
    }

    public function profile(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::guard('user')->user()->load('country','media','reviewStatistics')->loadCount(['orders' => function ($query) {
            $query->where('status', OrderStatusEnum::DONE);
        }]);

        return $this->respondWithResource(new UserResource($user), 'profile');
    }

    public function update(UpdateUserRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::guard('user')->user();

        // Start a database transaction
        $updatedUser = DB::transaction(static function () use ($user, $request) {
            // Update user details
//            if ($request->has('name')) {
//                $user->name = $request->name;
//            }
//            if ($request->has('email')) {
//                $user->email = $request->email;
//            }
//            if ($request->has('phone')) {
//                $user->phone = $request->phone;
//            }


            // Update user details individually
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('phone')) {
                $user->phone = $request->phone;
            }
            if ($request->has('country_id')) {
                $user->country_id = $request->country_id;
            }
            if ($request->has('gender')) {
                $user->gender = $request->gender == 'male' ? 1 : 0;
            }
            if ($request->has('date_of_birth')) {
                $user->date_of_birth = $request->date_of_birth;
            }

            if ($user->isDirty()) {
                $user->save();
            }

            // If the request has an image, add it to the user's profile
            if ($request->hasFile('personal')) {
                // Remove old personal image
                $user->clearMediaCollection('personal');

                // Add new personal image
                $user->addMediaFromRequest('personal')->toMediaCollection('personal');
            }

            return $user->load('country','media');
        });

        return $this->respondWithResource(new UserResource($updatedUser), 'Profile updated successfully');
    }

    public function banners()
    {
        // Directory where images are stored
        $directory = public_path('app/banners');

        // Get all files in the directory
        $files = array_diff(scandir($directory), ['.', '..']);

        // Create URLs for each image
        $imageUrls = array_map(function($file) use ($directory) {
            return asset('app/banners/' . $file);
        }, $files);

        // Wrap the URLs in MediaResource collection
//        return MediaResource::collection($imageUrls);
        return $this->respondWithResource(MediaResource::collection($imageUrls), 'banners');
    }

    public function home(): JsonResponse
    {
        $user= Auth::guard('user')->user();
        $userAction = UserAction::getShowRateLastOrderForUser($user->id);
        if ($userAction && $userAction->value == 1) {
            $reviewOrder = $userAction->model_id;
        } else {
            $reviewOrder = null;
        }
        return $this->apiResponse(
            [
                'success' => true,
                'result' => [
                    'review_order_id' => $reviewOrder,
                ],
                'message' => ''
            ], 200
        );
    }


    public function setNotificationSetting(Request $request): \Illuminate\Http\JsonResponse
    {
        $firebaseService = new FirebaseNotificationService();
        $setNotificationSetting = $request->input('notification',false);
        $firebaseService->setNotification($setNotificationSetting,$request->user());

        return $this->respondSuccess('Notification setting updated successfully');
    }

}
