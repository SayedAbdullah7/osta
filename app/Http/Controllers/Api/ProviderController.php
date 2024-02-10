<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
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

class ProviderController extends Controller
{
    use ApiResponseTrait;


    /**
     * Register a new user with phone number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerProvider(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:15',
            'last_name' => 'required|string|max:15',
//            'phone' => 'required|string|size:15|unique:providers',
            'phone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('providers')->where(function ($query) {
                    return $query->where('is_phone_verified', 1);
                }),
            ],
            'is_phone_verified' => 'boolean',
            'password' => 'required|string|confirmed|min:8',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'personal' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'front_id' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'back_id' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'certificate' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'service_id' => 'required|array|exists:services,id',
        ]);
//        return $request->service_id;

        $duplicatedProvider = Provider::where('phone', $request->phone)->notVerified()->first();
        if ($duplicatedProvider){
            $duplicatedProvider->delete();
        }
//        $duplicatedProviders->each(function ($provider) {
//            $provider->delete();
//        });


        $provider = new Provider();
        $provider->first_name = $request->input('first_name');
        $provider->last_name = $request->input('last_name');
        $provider->phone = $request->input('phone');
        $provider->password = Hash::make($request->password);
        $provider->country_id = $request->input('country_id');
        $provider->city_id = $request->input('city_id');
        $provider->save();
//        if($request->hasFile('image') && $request->file('image')->isValid()){
            $provider->addMediaFromRequest('personal')->toMediaCollection('personal');
            $provider->addMediaFromRequest('front_id')->toMediaCollection('front_id');
            $provider->addMediaFromRequest('back_id')->toMediaCollection('back_id');
            $provider->addMediaFromRequest('certificate')->toMediaCollection('certificate');
//        }
        $provider->services()->sync($request->service_id);


        return $this->respondWithResource(new ProviderResource($provider),'registered successfully');
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:8',
        ]);


        $provider = Provider::where('phone', $request->phone)->first();

        if (!$provider || !Hash::check($request->password, $provider->password)) {
            return $this->respondNotFound('invalid phone or password');
        }

        if (!$provider->isVerified()) {
            return $this->respondSuccess('OTP sent');
        }

        // Clear the OTP after successful login using OTPService
        OTPService::destroyOTPs($provider);

        $token = $provider->createToken('app-token')->plainTextToken;
        $provider->token = $token;
        return $this->respondWithResource(new ProviderResource($provider), 'login successfully', 200);
    }


    /**
     * Generate a new OTP for login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOTP(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
//            'phone' => 'required|string|exists:users',
            'phone' => 'required|string|max:15',
        ]);

        $provider = Provider::where('phone', $request->phone)->first();

        if (!$provider) {
            return $this->respondNotFound('Provider not found');
        }
        // Send verification code via SMS using
        $otpService = new OTPService();
        $code = $otpService->generateOTP($provider);

        return $this->respondSuccess('OTP sent');
//        return response()->json(['message' => 'OTP sent'], 200);
//        $token = $provider->createToken('app-token')->plainTextToken;
//        $provider->token = $token;
//        $otpService = new OTPService();
//        $code = $otpService->generateOTP($user);
    }

    public function verify(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'otp' => 'required|string',
        ]);

        $provider = Provider::where('phone', $request->phone)->first();
        if (!$provider) {
            return $this->respondNotFound('provider not found');
        }
        if (!$provider->isVerified()) {
            $provider->changeToVerify();
            $provider->save();
        }


        // Check if OTP is valid using OTPService
        if (!OTPService::verifyOTP($provider, $request->otp)) {
            return $this->respondError('Invalid OTP', 401);
        }

        // OTP is valid, log in the user
        // Auth::login($user);

        // Clear the OTP after successful login using OTPService
        OTPService::destroyOTPs($provider);

        $token = $provider->createToken('app-token')->plainTextToken;
        $provider->token = $token;
        return $this->respondWithResource(new ProviderResource($provider), 'login successfully', 200);
    }
    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'otp' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $provider = Provider::where('phone', $request->phone)->first();
        if (!$provider) {
            return $this->respondNotFound('provider not found');
        }
        if (!$provider->isVerified()) {
            $provider->changeToVerify();
            $provider->save();
        }

        // Check if OTP is valid using OTPService
        if (!OTPService::verifyOTP($provider, $request->otp)) {
            return $this->respondError('Invalid OTP', 401);
        }

        $provider->password = $request->password;
        $provider->save();

        // Clear the OTP after successful login using OTPService
        OTPService::destroyOTPs($provider);

        $token = $provider->createToken('app-token')->plainTextToken;
        $provider->token = $token;

        return $this->respondWithResource(new ProviderResource($provider), 'password updated successfully', 200);
    }



}
