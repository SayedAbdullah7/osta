<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SubscriptionServiceInterface;
use App\Http\Resources\ProviderSubscriptionResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    use ApiResponseTrait;
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

//    public function getProviderSubscriptionHistory($providerId)
//    {
//        return response()->json([
//            'subscription_history' => $this->subscriptionService->getProviderSubscriptionHistory($providerId)
//        ]);
//    }

//    public function cancelSubscription($providerId)
//    {
//        $success = $this->subscriptionService->cancelProviderSubscription($providerId);
//
//        return response()->json([
//            'status' => $success ? 'Subscription canceled' : 'No active subscription found'
//        ]);
//    }

//    /**
//     * @throws \Exception
//     */
//    public function getCurrentSubscription(Request $request)
//    {
//        $providerId = Auth::guard('provider')->user()->id;
////        $providerId = 1;
//         $providerSubscription = $this->subscriptionService->getProviderLastValidSubscription($providerId);
//        if (!$providerSubscription) {
//            return  $this->respondNoContentResource('no active subscription found');
////            return   $this->respondNotFound();
//        }
//        return new ProviderSubscriptionResource($providerSubscription);
//    }
//    public function renewSubscription(Request $request,$subscriptionId)
//    {
//
////        $providerId = $request->input('provider_id');
//        $providerId = Auth::guard('provider')->user()->id;
////        $subscriptionId = $request->input('subscription_id');
//
//        return  $this->subscriptionService->renewProviderSubscription($providerId, $subscriptionId);
//
//        return response()->json([
//            'status' => 'Subscription renewed successfully'
//        ]);
//    }

//    public function getAllActiveSubscriptions()
//    {
//        $activeSubscriptions = $this->subscriptionService->getAllActiveSubscriptions();
//        return response()->json($activeSubscriptions);
//    }

    public function getLastActiveSubscription()
    {
        try {
            $lastActiveSubscription = $this->subscriptionService->getLastActiveSubscription();

            if (!$lastActiveSubscription) {
                return response()->json(['message' => 'No active subscription found'], 404);
            }

            // Returning the subscription using a resource if needed
//            return new SubscriptionResource($lastActiveSubscription);
            return $this->respondWithResource(new \App\Http\Resources\SubscriptionResource($lastActiveSubscription));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve the last active subscription', 'error' => $e->getMessage()], 500);
        }
        $activeSubscriptions = $this->subscriptionService->getLastActiveSubscription();
        return $this->respondWithResource(new \App\Http\Resources\SubscriptionResource($activeSubscriptions));
        return response()->json($activeSubscriptions);
    }


    public function getProviderSubscriptionHistory($providerId)
    {
        $subscriptionHistory = $this->subscriptionService->getProviderSubscriptionHistory($providerId);
        return response()->json(['subscription_history' => $subscriptionHistory]);
    }

    public function cancelSubscription($providerId)
    {
        try {
            $this->subscriptionService->cancelProviderSubscription($providerId);
            return response()->json(['status' => 'Subscription canceled successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => $e->getMessage()], 400);
        }
    }

    public function getCurrentSubscription(Request $request)
    {
        $providerId = Auth::guard('provider')->user()->id;

        try {
            $providerSubscription = $this->subscriptionService->getProviderLastValidSubscription($providerId);
//            return new ProviderSubscriptionResource($providerSubscription);
            return $this->respondWithResource(new \App\Http\Resources\ProviderSubscriptionResource($providerSubscription));
        } catch (\Exception $e) {
            return  $this->respondNoContentResource('no active subscription found');
//            return response()->json(['message' => 'No active subscription found'], 404);
        }
    }

    public function renewSubscription(Request $request, $subscriptionId): ?\Illuminate\Http\JsonResponse
    {
        $providerId = Auth::guard('provider')->user()->id;

        try {
            $providerSubscription =$this->subscriptionService->renewProviderSubscription($providerId, $subscriptionId);

            return $this->respondWithResource(new \App\Http\Resources\ProviderSubscriptionResource($providerSubscription));
//            return response()->json(['status' => 'Subscription renewed successfully']);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
            return response()->json(['status' => $e->getMessage()], 400);
        }
    }

    public function getAllActiveSubscriptions()
    {
        $activeSubscriptions = $this->subscriptionService->getAllSubscriptions();
        return response()->json($activeSubscriptions);
    }

}
