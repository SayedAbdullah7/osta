<?php

namespace App\Repositories;
use App\Models\Offer;

class OfferRepository
{
    public function getOffersForProvider(int $providerId): \Illuminate\Database\Eloquent\Collection|array
    {
        return Offer::with(['order', 'order.user', 'order.service', 'order.subServices'])
            ->where('provider_id', $providerId)
            ->get();
    }

    public function createOffer(array $data)
    {
        return Offer::create($data);
    }

    public function findOffer(int $providerId, int $orderId, string $status, bool $isSecond = false)
    {
        return Offer::where('provider_id', $providerId)
            ->where('order_id', $orderId)
            ->where('status', $status)
            ->where('is_second', $isSecond)
            ->first();
    }

    public function countOffersByProvider(int $providerId, string $status)
    {
        return Offer::where('provider_id', $providerId)
            ->where('status', $status)
            ->count();
    }

    public function countOffersByOrder(int $orderId, string $status)
    {
        return Offer::where('order_id', $orderId)
            ->where('status', $status)
            ->count();
    }

    public function countOffersForProviderOrder(int $providerId, int $orderId)
    {
        return Offer::where('provider_id', $providerId)
            ->where('order_id', $orderId)
            ->where(function ($query) {
                $query->where('status', OrderStatusEnum::PENDING)
                    ->orWhere(function ($query2) {
                        $query2->where('status', OrderStatusEnum::REJECTED)
                            ->where('is_second', true);
                    });
            })
            ->count();
    }

    public function removeOldOffers(int $orderId = null, int $providerId = null)
    {
        Offer::when($orderId, function ($query) use ($orderId) {
            $query->where('order_id', $orderId);
        })->when($providerId, function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', OrderStatusEnum::PENDING)
            ->where('created_at', '<', now()->subMinutes(ProviderOfferService::MaxOfferTime))
            ->delete();
    }
}
