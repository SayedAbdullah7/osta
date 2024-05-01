<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Provider;
use App\Services\OTPService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProviderController extends Controller
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

        // Check if the provider is verified and has the provided phone number
        $provider = Provider::verified()->where('phone', $request->phone)->first();

        // If the provider does not exist, respond with a success message
        if (!$provider) {
            return $this->respondSuccess('complete register process');
        }

        // Generate an OTP for the provider
        $otpService = new OTPService();
        $otpService->generateOTP($provider);
        return $this->respondSuccess('OTP send');
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
        $provider->email = $request->input('email');
        $provider->gender = $request->input('gender') == "male" ? 1 : 0;
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
        if ($request->bank_account_name && $request->bank_account_iban){
        $provider->bank_account()->create([
            'name' => $request->bank_account_name,
            'iban' => $request->bank_account_iban,
        ]);
    }
}

    /**
     * Register a new provider with phone number.
     *
     * @param RegisterProviderRequest $request
     * @return JsonResponse
     */
    public
    function registerProvider(RegisterProviderRequest $request): JsonResponse
    {
        $provider = DB::transaction(function () use ($request) {

            $this->deleteDuplicateProviders($request->phone);

            $provider = $this->createProvider($request);
            $this->handleMediaUploads($provider, $request);
            $this->syncServicesAndBankAccount($provider, $request);

            $otpService = new OTPService();
            $otpService->generateOTP($provider);
            return $provider;
        });

        return $this->respondWithResource(new ProviderResource($provider), 'registered successfully, and otp sent');
    }

    /**
     * Login provider with OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function login(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'otp' => 'required|string',
        ]);

        // Find the provider
        $provider = $this->getProviderByPhone($request->phone);
        if (!$provider) {
            return $this->respondNotFound('Provider not found');
        }

        // Check if the OTP is valid
        if (!OTPService::verifyOTP($provider, $request->otp)) {
            return $this->respondError('Invalid OTP', 401);
        }

        // If the provider is not verified, verify them
        if (!$provider->isVerified()) {
            $provider->changeToVerify();
            $provider->save();
        }

        // Clear the OTP after successful login
        OTPService::destroyOTPs($provider);

        // Create a new token for the provider
        $token = $provider->createToken('app-token')->plainTextToken;
        $provider->token = $token;
        return $this->respondWithResource(new ProviderResource($provider), 'login successfully', 200);
    }


    /**
     * Generate a new OTP for login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function generateOTP(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
        ]);

        // Find the provider
        $provider = $this->getProviderByPhone($request->phone);

        // If the provider does not exist, respond with an error
        if (!$provider) {
            return $this->respondNotFound('Provider not found');
        }

        // Generate an OTP for the provider
        $otpService = new OTPService();
        $code = $otpService->generateOTP($provider);
        return $this->respondSuccess('OTP sent');
    }

    /**
     * Get a provider by their phone number.
     *
     * @param $phone
     * @return mixed
     */
    protected
    function getProviderByPhone($phone)
    {
        return Provider::where('phone', $phone)->first();
    }


}
