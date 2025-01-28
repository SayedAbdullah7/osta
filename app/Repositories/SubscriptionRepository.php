<?php

namespace App\Repositories;

use App\Models\Provider;
use App\Models\ProviderSubscription;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionRepository
{

    /**
     * Get the last active subscription.
     *
     * @return Subscription|null
     */
    public function getLastActiveSubscription()
    {
        return Subscription::where('is_available', true)
            ->orderBy('id', 'desc')
            ->first();
    }
    public function getAllAvailableSubscriptions()
    {
        return Subscription::where('is_available', true)
            ->orderBy('id', 'desc')
            ->take(1)
//            ->with('level')
            ->get();
    }

    public function findSubscriptionById($id)
    {
        return Subscription::where('is_available', true)
            ->find($id);
    }

    public function subscribeProvider($providerId, $subscriptionId, $startDate, $endDate)
    {
        return ProviderSubscription::create([
            'provider_id' => $providerId,
            'subscription_id' => $subscriptionId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function getLastValidSubscription($providerId)
    {
        return ProviderSubscription::where('provider_id', $providerId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderBy('end_date', 'desc')
            ->first();
    }

    public function getSubscriptionHistory($providerId)
    {
        return ProviderSubscription::where('provider_id', $providerId)
            ->orderBy('end_date', 'desc')
            ->get();
    }

    public function updateSubscriptionEndDate($subscriptionId, $newEndDate)
    {
        return ProviderSubscription::where('subscription_id', $subscriptionId)
            ->update(['end_date' => $newEndDate]);
    }

    public function cancelSubscription($providerId, $subscriptionId)
    {
        return ProviderSubscription::where('provider_id', $providerId)
            ->where('subscription_id', $subscriptionId)
            ->update(['end_date' => now()]);
    }

}
