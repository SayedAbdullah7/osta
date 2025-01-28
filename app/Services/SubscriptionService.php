<?php

namespace App\Services;

use App\Repositories\SubscriptionRepository;
use Carbon\Carbon;

class SubscriptionService
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function getAllSubscriptions()
    {
        return $this->subscriptionRepository->getAllAvailableSubscriptions();
    }

    public function getProviderLastValidSubscription($providerId)
    {
        return $this->subscriptionRepository->getLastValidSubscription($providerId);
    }

    public function subscribeProvider($providerId, $subscriptionId)
    {
        $activeSubscription = $this->getProviderLastValidSubscription($providerId);

        if ($activeSubscription) {
            throw new \Exception('Provider already has an active subscription.');
        }

        $subscription = $this->subscriptionRepository->findSubscriptionById($subscriptionId);
        $startDate = now();
        $endDate = now()->addDays($subscription->number_of_days);

        return $this->subscriptionRepository->subscribeProvider($providerId, $subscriptionId, $startDate, $endDate);
    }

    public function renewProviderSubscription($providerId, $subscriptionId)
    {
        $subscription = $this->subscriptionRepository->findSubscriptionById($subscriptionId);
        $validSubscription = $this->getProviderLastValidSubscription($providerId);

        $startDate = now();
        $endDate = now()->addDays($subscription->number_of_days);

        if ($validSubscription) {
            $endDate = Carbon::parse($validSubscription->end_date)->addDays($subscription->number_of_days);
        }

        return $this->subscriptionRepository->subscribeProvider($providerId, $subscriptionId, $startDate, $endDate);
    }

    public function getProviderSubscriptionHistory($providerId)
    {
        return $this->subscriptionRepository->getSubscriptionHistory($providerId);
    }

    public function cancelProviderSubscription($providerId)
    {
        $activeSubscription = $this->getProviderLastValidSubscription($providerId);

        if (!$activeSubscription) {
            throw new \Exception('No active subscription found.');
        }

        return $this->subscriptionRepository->cancelSubscription($providerId, $activeSubscription->subscription_id);
    }

    /**
     * Get the last active subscription from the Subscription model
     *
     * @return \App\Models\Subscription|null
     */
    public function getLastActiveSubscription()
    {
        return $this->subscriptionRepository->getLastActiveSubscription();
    }
    public function getAllAvailableSubscriptions()
    {
        return $this->subscriptionRepository->getAllAvailableSubscriptions();
    }
}
