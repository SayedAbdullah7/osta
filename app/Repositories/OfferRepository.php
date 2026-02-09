<?php

namespace App\Repositories;

use App\Enums\OfferStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Offer;
use App\Services\ProviderOfferService;
use Illuminate\Database\Eloquent\Collection;

class OfferRepository
{
    public function getOffersForProvider(int $providerId): \Illuminate\Database\Eloquent\Collection|array
    {
        return Offer::with(['order', 'order.user', 'order.service', 'order.orderSubServices'])
            ->where('provider_id', $providerId)
            ->get();
    }

    /**
     * @param int $providerId
     * @return Collection|array
     */
    public function getPendingOffersForProvider(int $providerId): \Illuminate\Database\Eloquent\Collection|array
    {
        return Offer::with(['order', 'order.user', 'order.service', 'order.orderSubServices'])
            ->where('provider_id', $providerId)->where('status', OfferStatusEnum::PENDING)
            ->get();
    }

    public function getAllOffersForProviderPaginated(int $providerId,$page,$perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator
    {
        return Offer::with(['order', 'order.user', 'order.service', 'order.orderSubServices'])
            ->where('provider_id', $providerId)
            ->WhereNot( function($query) {
                $query
                    ->rejected()
                    ->IsSecond();
            })
            ->whereHas('order', static function ($query) {
                return $query->whereNot('status', OrderStatusEnum::DONE)->whereNot('status', OrderStatusEnum::CANCELED);
            })
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function createOffer(array $data): Offer
    {
        return Offer::create($data);
    }

    public function findOffer(int $providerId, int $orderId, OfferStatusEnum $status, bool $isSecond = false): Offer|null
    {
        return Offer::where('provider_id', $providerId)
            ->where('order_id', $orderId)
            ->where('status', $status)
            ->where('is_second', $isSecond)
            ->first();
    }

    public function updateOffer(Offer $offer, array $data)
    {
        $offer->update($data);
    }

    public function countOffersByProvider(int $providerId, string $status): int
    {
        return Offer::where('provider_id', $providerId)
            ->where('status', $status)
            ->count();
    }

    public function countOffersByOrder(int $orderId, string $status): int
    {
        return Offer::where('order_id', $orderId)
            ->where('status', $status)
            ->count();
    }

    public function countOffersForProviderOrder(int $providerId, int $orderId): int
    {
        return Offer::where('provider_id', $providerId)
            ->where('order_id', $orderId)
            ->where(function ($query) {
                $query->where('status', OfferStatusEnum::PENDING)
                    ->orWhere(function ($query2) {
                        $query2->where('status', OfferStatusEnum::REJECTED)
                            ->where('is_second', true);
                    });
            })
            ->count();
    }

    public function removeOldOffers(int $orderId = null, int $providerId = null): void
    {
        Offer::when($orderId, function ($query) use ($orderId) {
            $query->where('order_id', $orderId);
        })->when($providerId, function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', OfferStatusEnum::PENDING)
            ->where('created_at', '<', now()->subMinutes(ProviderOfferService::MAX_OFFER_TIME))
            ->delete();
    }
}
