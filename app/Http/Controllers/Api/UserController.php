<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Provider;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function checkPhone(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
        ]);


        $user = User::verified()->where('phone', $request->phone)->first();

        if (!$user) {
            return $this->respondSuccess('complete register process');
        }

        $otpService = new OTPService();
        $code = $otpService->generateOTP($user);
        return $this->respondSuccess('OTP send');
    }

    /**
     * Register a new user with phone number.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'email|unique:users|max:255',
            'phone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('is_phone_verified', 1);
                }),
            ],
            'country_id' => 'exists:countries,id',
            'gender' => 'required|in:male,female',
        ]);

        User::where('phone', $request->phone)->notVerified()->delete();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->country_id = $request->country_id;
        $user->gender = $request->gender = 'male' ? 1 : 0;
        $user->save();

        // Send verification code via SMS using
        $otpService = new OTPService();
        $code = $otpService->generateOTP($user);


        return $this->respondWithResource(new UserResource($user), 'registered successfully, and otp sent');
    }

    /**
     * Login user with OTP.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'otp' => 'required|string',
        ]);


        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return $this->respondNotFound('User not found');
        }
        if (!$user->isVerified()) {
            $user->changeToVerify();
            $user->save();
        }


        // Check if OTP is valid using OTPService
        if (!OTPService::verifyOTP($user, $request->otp)) {
            return $this->respondError('Invalid OTP', 401);
        }

        // OTP is valid, log in the user
//        Auth::login($user);

        // Clear the OTP after successful login using OTPService
        OTPService::destroyOTPs($user);

        $token = $user->createToken('app-token')->plainTextToken;
        $user->token = $token;
        return $this->respondWithResource(new UserResource($user), 'login successfully', 200);
    }

    /**
     * Generate a new OTP for login.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOTP(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return $this->respondNotFound('User not found');
        }

        $otpService = new OTPService();
        $code = $otpService->generateOTP($user);
        return $this->respondSuccess('OTP sent');
    }

}
