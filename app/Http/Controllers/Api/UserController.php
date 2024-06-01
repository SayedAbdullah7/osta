<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Location;
use App\Models\Provider;
use App\Models\User;
use App\Services\OTPService;
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
            return $this->respondSuccess('complete register process');
        }

        // Generate an OTP for the user
        $otpService = new OTPService();
        $otpService->generateOTP($user);
        return $this->respondSuccess('OTP send');
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

        // Create a new token for the user
        $token = $user->createToken('app-token')->plainTextToken;
        $user->token = $token;
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
}
