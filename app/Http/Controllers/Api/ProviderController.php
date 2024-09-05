<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ProviderResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Invoice;
use App\Models\Order;
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
            return $this->respondSuccessWithData('complete register process',false );
//            return $this->respondSuccess('complete register process');
        }

        // Generate an OTP for the provider
        $otpService = new OTPService();
        $otpService->generateOTP($provider);
        return $this->respondSuccessWithData('OTP send',true );
//        return $this->respondSuccess('OTP send');

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
            if ($request->hasFile($field)) {
                $provider->clearMediaCollection($field); // Remove old media
                $provider->addMediaFromRequest($field)->toMediaCollection($field);
            }
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
    public function registerProvider(RegisterProviderRequest $request): JsonResponse
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

    public function profile(): JsonResponse
    {
        $provider = Auth::guard('provider')->user()->load('country','media','services')->loadCount('orders');

        return $this->respondWithResource(new ProviderResource($provider), 'provider profile');
    }

    public function update(UpdateProviderRequest $request): JsonResponse
    {
        $provider = Auth::guard('provider')->user()->load('country','media','services')->loadCount('orders');

        $updatedProvider = DB::transaction(function () use ($provider, $request) {
            $this->updateProviderModel($provider, $request);
            $this->handleMediaUploads($provider, $request);
            if ($request->service_id && !empty($request->service_id)) {
                $this->syncServicesAndBankAccount($provider, $request);
            }

            return $provider;
        });

        return $this->respondWithResource(new ProviderResource($updatedProvider), 'Profile updated successfully');
    }
    protected function updateProviderModel(Provider $provider, $request): void
    {
        if ($request->has('first_name')) {
            $provider->first_name = $request->input('first_name');
        }

        if ($request->has('last_name')) {
            $provider->last_name = $request->input('last_name');
        }

        if ($request->has('email')) {
            $provider->email = $request->input('email');
        }

        if ($request->has('gender')) {
            $provider->gender = $request->input('gender') == "male" ? 1 : 0;
        }
//        $provider->first_name = $request->input('first_name');
//        $provider->last_name = $request->input('last_name');
////        $provider->phone = $request->input('phone');
//        $provider->email = $request->input('email');
//        $provider->gender = $request->input('gender') == "male" ? 1 : 0;
        if ($request->has('country_id')) {
            $provider->country_id = $request->input('country_id');
        }
        if ($request->has('city_id')) {
            $provider->city_id = $request->input('city_id');
        }
        if ($provider->isDirty()) {
            $provider->save();
        }
    }
    // show statictes
    public function home(): JsonResponse
    {
        return $this->apiResponse(
            [
                'success' => true,
                'result' => [
                    'total_completed_orders_today' => $this->getTotalCompletedOrdersToday(),
                    // erring form orders
                    'total_erring_form_completed_orders_today' => $this->getTotalErringFormOrdersCompletedToday(),

                ],
                'message' => ''
            ], 200
        );
    }

    private function getTotalCompletedOrdersToday()
    {
        return Order::where('provider_id', Auth::guard('provider')->user()->id)
            ->where('status', OrderStatusEnum::DONE)
            ->whereDate('created_at', today())
            ->count();
    }
    private function getTotalErringFormOrdersCompletedToday()
    {
        return Invoice::whereHas('order', function ($q) {
            $q->where('provider_id', Auth::guard('provider')->user()->id)
                ->where('status', OrderStatusEnum::DONE)
                ->whereDate('created_at', today());
        })->sum('provider_earning');
    }

}
