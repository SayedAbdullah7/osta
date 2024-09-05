<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\UserResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Provider;
use App\Models\User;
use App\Services\OTPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OLDProviderController extends Controller
{
    use ApiResponseTrait;

    protected const MAX_IMAGE_SIZE = 5120;

    protected $registrationRules;
    protected $loginRules;

    public function __construct()
    {
        $this->loginRules = [
            'phone' => 'required|string|max:15',
            'password' => 'required|string|min:8',
        ];
    }

    /**
     * Method to delete duplicate providers with unverified phone numbers
     */
    protected function deleteDuplicateProviders($phone): void
    {
        $duplicatedProvider = Provider::where('phone', $phone)->notVerified()->first();
        if ($duplicatedProvider) {
            $duplicatedProvider->delete();
        }
    }


    protected function createProvider(Request $request): Provider
    {
        $provider = new Provider();
        $provider->first_name = $request->input('first_name');
        $provider->last_name = $request->input('last_name');
        $provider->phone = $request->input('phone');
        $provider->password = Hash::make($request->password);
        $provider->country_id = $request->input('country_id');
        $provider->city_id = $request->input('city_id');
        $provider->save();

        return $provider;
    }

    /**
     * Method to handle media uploads for a provider
     */
    protected function handleMediaUploads(Provider $provider, Request $request): void
    {
        $mediaFields = ['personal', 'front_id', 'back_id', 'certificate'];

        foreach ($mediaFields as $field) {
            $provider->addMediaFromRequest($field)->toMediaCollection($field);
        }
    }


    protected function syncServicesAndBankAccount(Provider $provider, Request $request): void
    {
        $provider->services()->sync($request->service_id);
        $provider->bank_account()->create([
            'name' => $request->bank_account_name,
            'iban' => $request->bank_account_iban,
        ]);
    }

    /**
     * Register a new user with phone number.
     *
     * @param RegisterProviderRequest $request
     * @return JsonResponse
     */
    public function registerProvider(RegisterProviderRequest $request): JsonResponse
    {
        $this->deleteDuplicateProviders($request->phone);

        $provider = $this->createProvider($request);
        $this->handleMediaUploads($provider, $request);
        $this->syncServicesAndBankAccount($provider, $request);

        $otpService = new OTPService();
        $code = $otpService->generateOTP($provider);

        return $this->respondWithResource(new ProviderResource($provider), 'registered successfully, and otp sent');
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate($this->loginRules);


        $provider = Provider::where('phone', $request->phone)->first();

        if (!$provider || !Hash::check($request->password, $provider->password)) {
            return $this->respondUnauthorized('Invalid phone or password');
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
     * @param Request $request
     * @return JsonResponse
     */
    public function generateOTP(Request $request): JsonResponse
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

        // Check if OTP is valid using OTPService
        if (!OTPService::verifyOTP($provider, $request->otp)) {
            return $this->respondError('Invalid OTP', 401);
        }

        if (!$provider->isVerified()) {
            $provider->changeToVerify();
            $provider->save();
        }

        // OTP is valid, log in the user
        // Auth::login($user);

        // Clear the OTP after successful login using OTPService
        OTPService::destroyOTPs($provider);

        $token = $provider->createToken('app-token')->plainTextToken;
        $provider->token = $token;
        return $this->respondWithResource(new ProviderResource($provider), 'login successfully', 200);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
//            'phone' => 'required|string|max:15',
//            'otp' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

//        $provider = Provider::where('phone', $request->phone)->first();
        $provider = $request->user();
        if (!$provider) {
            return $this->respondNotFound('provider not found');
        }
        if (!$provider->isVerified()) {
            $provider->changeToVerify();
            $provider->save();
        }


//        // Check if OTP is valid using OTPService
//        if (!OTPService::verifyOTP($provider, $request->otp)) {
//            return $this->respondError('Invalid OTP', 401);
//        }

        $provider->password = Hash::make($request->password);
        $provider->save();

        // Clear the OTP after successful login using OTPService
//        OTPService::destroyOTPs($provider);

        $token = $provider->createToken('app-token')->plainTextToken;
        $provider->token = $token;

        return $this->respondWithResource(new ProviderResource($provider), 'password updated successfully', 200);
    }


}
