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

class AuthController extends Controller
{
    use ApiResponseTrait;
    /**
     * Register a new user with phone number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUser(Request $request)
    {
        $validatedData = $request->validate([
//            'name' => 'required|string|max:255',
//            'email' => 'email|max:255',
            'email' => 'email|unique:users|max:255',
            'phone' => 'required|string|unique:users|max:15',
//            'phone' => 'required|string|max:15',
//            'account' => 'required|in:evidence,specialization,bank account',
            'country_id' => 'exists:countries,id',
            'gender' => 'required|in:male,female',
        ]);
//        return $request->all();
//        $validator = Validator::make($request->all(), [
//            'name' => 'required|string|max:255',
//            'email' => 'email|unique:users|max:255',
//            'phone' => 'required|string|unique:users|max:15',
//        ]);

//        if ($validator->fails()) {
//            return response()->json(['error' => $validator->errors()], 422);
//        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->country_id = $request->country_id;
//        $user->account = $request->account;
        $user->gender = $request->gender='male'?1:0;
        $user->save();

        // Send verification code via SMS using
        $otpService = new OTPService();
        $code = $otpService->generateOTP($user);


        return $this->respondWithResource(new UserResource($user),'registered successfully');
//        return response()->json(['token' => $token], 201);
    }

    /**
     * Register a new user with phone number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerProvider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
//            'email' => 'email|unique:providers|max:255',
            'phone' => ['required|string|max:15',
                Rule::unique('providers')->where(function ($query) {
                    return $query->where('is_phone_verified', 1);
                }),],
            'password' => 'required|string|confirmed|max:255',
        ]);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        Provider::where('phone', $request->phone)->notVerified()->delete();

        $user = Provider::create([
            'name' => $request->name,
//            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);


        // Send verification code via SMS using
        $otpService = new OTPService();
        $code = $otpService->generateOTP($user);

        $token = $user->createToken('app-token')->plainTextToken;
        $user->token = $token;
        return $this->respondWithResource(new UserResource($user),'login successfully');

        return response()->json(['token' => $token], 201);
    }

    /**
     * Login user with OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginUser(Request $request)
    {
//        $validator = Validator::make($request->all(), [
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'otp' => 'required|string',
        ]);

//        if ($validator->fails()) {
//            return response()->json(['error' => $validator->errors()], 422);
//        }

        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return $this->respondNotFound('User not found');
//            return response()->json(['error' => 'User not found'], 404);
        }


        // Check if OTP is valid using OTPService
        if (!OTPService::verifyOTP($user, $request->otp)) {
            return $this->respondError('Invalid OTP',401);
//            return response()->json(['error' => 'Invalid OTP'], 401);
        }

        // OTP is valid, log in the user
//        Auth::login($user);

        // Clear the OTP after successful login using OTPService
        OTPService::destroyOTPs($user);

        $token = $user->createToken('app-token')->plainTextToken;
        $user->token = $token;
        return $this->respondWithResource(new UserResource($user),'login successfully',200);

//        return response()->json(['token' => $token], 200);

    }

    /**
     * Generate a new OTP for login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOTPUser(Request $request): \Illuminate\Http\JsonResponse
    {
//        $validator = Validator::make($request->all(), [
        $validatedData = $request->validate([
//            'phone' => 'required|string|exists:users',
            'phone' => 'required|string|max:15',
        ]);

//        if ($validator->fails()) {
//            return response()->json(['error' => $validator->errors()], 422);
//        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return $this->respondNotFound('User not found');
//            return response()->json(['error' => 'User not found'], 404);
        }

        $otpService = new OTPService();
        $code = $otpService->generateOTP($user);
        return $this->respondSuccess('New OTP generated');
//        return response()->json(['message' => 'New OTP generated'], 200);
    }

}
