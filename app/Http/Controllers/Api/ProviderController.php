<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\LevelResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\ProviderLevelResource;
use App\Http\Resources\ProviderResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Invoice;
use App\Models\Level;
use App\Models\Order;
use App\Models\Provider;
use App\Services\AccountDeletionService;
use App\Services\FirebaseNotificationService;
use App\Services\LevelEvaluationService;
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
        $provider->name = $request->input('name');
//        $provider->first_name = $request->input('first_name');
//        $provider->last_name = $request->input('last_name');
        $provider->phone = $request->input('phone');
        $provider->email = $request->input('email');
        $provider->gender = $request->input('gender') == "male" ? 1 : 0;
        $provider->country_id = $request->input('country_id',1);
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
            'firebase_token' => 'required|string', // Firebase token is required
            'is_ios' => 'required|boolean', // Determine if the device is iOS
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

        DB::transaction(function () use ($provider, $request) {
            // Delete duplicate tokens
//            \App\Models\DeviceToken::where('token', $request->firebase_token)->orWhere('provider_id', $provider->id)->delete();
            \App\Models\DeviceToken::where('token', $request->firebase_token)->delete();

            // Save the new token
            \App\Models\DeviceToken::create([
                'provider_id' => $provider->id, // Ensure this is null for users
                'token' => $request->firebase_token,
                'is_ios' => $request->input('is_ios', false),
            ]);
        });
        AccountDeletionService::cancelDeletionRequest($provider);

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
        $provider = Auth::guard('provider')->user()
            ->load([
                'country',
                'media',
                'services',
                'reviewStatistics',
                'reviewsReceived',
            ])
            ->loadCount('orders');

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
            $provider->is_approved = 0;
            return $provider;
        });

        return $this->respondWithResource(new ProviderResource($updatedProvider), 'Profile updated successfully');
    }
    protected function updateProviderModel(Provider $provider, $request): void
    {
        if ($request->has('name')) {
            $provider->name = $request->input('name');
        }
//        if ($request->has('first_name')) {
//            $provider->first_name = $request->input('first_name');
//        }

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
    public function home()
    {
        $provider = auth()->guard('provider')->user();
//        $provider->get
         app(LevelEvaluationService::class)->evaluateProvider($provider);
//        app(LevelEvaluationService::class)->ensureProviderHasLevel($provider);

        $provider->load([ 'currentMonthMetrics']);

        $allLevels = Level::orderBy('level')->get();
        return $this->apiResponse(
            [
                'success' => true,
                'result' => [
                    'today' => [
                        'completed_orders' => $this->getTotalCompletedOrdersToday(),
                        'earnings' => $this->getTotalEarningsToday(),
                    ],
                    'week' => [
                        'completed_orders' => $this->getTotalCompletedOrdersThisWeek(),
                        'earnings' => $this->getTotalEarningsThisWeek(),
                    ],
                    'month' => [
                        'completed_orders' => $this->getTotalCompletedOrdersThisMonth(),
                        'earnings' => $this->getTotalEarningsThisMonth(),
                    ],
                    'year' => [
                        'completed_orders' => $this->getTotalCompletedOrdersThisYear(),
                        'earnings' => $this->getTotalEarningsThisYear(),
                    ],
                    'levels' => LevelResource::collection($allLevels)->additional([
                        'currentLevelId' => 1,
                    ]),
                    'provider_stats' => new ProviderLevelResource($provider),
//                    'total_completed_orders_today' => $this->getTotalCompletedOrdersToday(),
                    // erring form orders
//                    'total_erring_form_completed_orders_today' => $this->getTotalErringFormOrdersCompletedToday(),
                    'unpaid_invoice' => $this->getPendingInvoices(),

                ],
                'message' => ''
            ], 200
        );
    }

//    private function getTotalCompletedOrdersToday()
//    {
//        return Order::where('provider_id', Auth::guard('provider')->user()->id)
//            ->where('status', OrderStatusEnum::DONE)
//            ->whereDate('created_at', today())
//            ->count();
//    }
//    private function getTotalErringFormOrdersCompletedToday()
//    {
//        return Invoice::whereHas('order', function ($q) {
//            $q->where('provider_id', Auth::guard('provider')->user()->id)
//                ->where('status', OrderStatusEnum::DONE)
//                ->whereDate('updated_at', today());
//        })->sum('provider_earning');
//    }
    private function getOrderStatsByPeriod(string $period)
    {
        $query = Order::where('provider_id', Auth::guard('provider')->user()->id)
            ->where('status', OrderStatusEnum::DONE);

        switch ($period) {
            case 'day':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;
            case 'year':
                $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                break;
        }

        return $query->count();
    }

    private function getEarningsStatsByPeriod(string $period)
    {
        $query = Invoice::whereHas('order', function ($q) use ($period) {
            $q->where('provider_id', Auth::guard('provider')->user()->id)
                ->where('status', OrderStatusEnum::DONE);

            switch ($period) {
                case 'day':
                    $q->whereDate('updated_at', today());
                    break;
                case 'week':
                    $q->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $q->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'year':
                    $q->whereBetween('updated_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
            }
        });

        return $query->sum('provider_earning');
    }

// Public methods for each time period
    public function getTotalCompletedOrdersToday()
    {
        return $this->getOrderStatsByPeriod('day');
    }

    public function getTotalCompletedOrdersThisWeek()
    {
        return $this->getOrderStatsByPeriod('week');
    }

    public function getTotalCompletedOrdersThisMonth()
    {
        return $this->getOrderStatsByPeriod('month');
    }
    private function getTotalCompletedOrdersThisYear()
    {
        return $this->getOrderStatsByPeriod('year');
    }

    private function getTotalEarningsThisYear()
    {
        return $this->getEarningsStatsByPeriod('year');
    }

    public function getTotalEarningsToday()
    {
        return $this->getEarningsStatsByPeriod('day');
    }

    public function getTotalEarningsThisWeek()
    {
        return $this->getEarningsStatsByPeriod('week');
    }

    public function getTotalEarningsThisMonth()
    {
        return $this->getEarningsStatsByPeriod('month');
    }

    public function getPendingInvoices()
    {
        $providerId = Auth::guard('provider')->user()->id;


//        return Invoice::where('payment_status', 'unpaid')->whereHas('order', function ($query) use ($providerId) {
//            $query->where('status', OrderStatusEnum::ACCEPTED)->where('provider_id',$providerId);
//        })->orderBy('id', 'desc')->first();
        $invoice = Invoice::where('is_sent', 1)->whereHas('order', function ($query) use ($providerId) {
            $query->where('status', OrderStatusEnum::ACCEPTED)->where('provider_id',$providerId);
        })->orderBy('id', 'desc')->first();
        if (!$invoice) {
            return null;
        }
        return new InvoiceResource($invoice);
    }

    public function setNotificationSetting(Request $request): \Illuminate\Http\JsonResponse
    {
        $firebaseService = new FirebaseNotificationService();
        $setNotificationSetting = $request->input('notification',false);
        $firebaseService->setNotification($setNotificationSetting,$request->user());

        return $this->respondSuccess('Notification setting updated successfully');
    }


    public function markAsNotNew()
    {
        $provider = Auth::guard('provider')->user();
        if ($provider->is_new) {
            $provider->is_new = false;
            $provider->save();
        }
        return $this->respondSuccess('Provider marked as not new');
    }

}
