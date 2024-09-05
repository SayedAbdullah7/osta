<?php
namespace App\Services;

use App\Enums\OfferStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Order;
use App\Repositories\OfferRepository;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ProviderOfferService
{
    use ApiResponseTrait;

    public const MAX_OFFERS_PER_ORDER = 7;
    public const MAX_OFFERS_PER_PROVIDER = 12;
    public const  MAX_OFFER_TIME = 10; // in minutes

    protected $offerRepository;
    protected $orderRepository;

    public function __construct(OfferRepository $offerRepository, OrderRepository $orderRepository)
    {
        $this->offerRepository = $offerRepository;
        $this->orderRepository = $orderRepository;
    }

    public function getOffersForProvider(int $providerId): JsonResponse
    {
        $offers = $this->offerRepository->getOffersForProvider($providerId);
        return $this->respondWithResource(OfferResource::collection($offers));
    }

    public function sendOfferFromProvider(array $data): JsonResponse
    {
        $orderId = $data['order_id'];
        $providerId = $data['provider_id'];
        $price = $data['price'];

        $order = $this->orderRepository->find($orderId);

        if (!$order || !$this->isOrderAvailableForOffer($order)) {
            return $this->respondNotFound('Order not found or not available for sending an offer');
        }

        if (!$this->canProviderOfferForOrder($providerId, $orderId)) {
            return $this->respondError('Cannot send more than one offer for the same order');
        }

        if (!$this->canProviderSendMoreOffers($providerId)) {
            return $this->respondError('Maximum number of offers for the provider reached');
        }

        if (!$this->canOrderAcceptMoreOffers($order)) {
            return $this->respondError('Maximum number of offers for the order reached');
        }

        $oldOffer = $this->offerRepository->findOffer($providerId, $orderId, OfferStatusEnum::REJECTED, false);

        $time = $data['time'] ?? 'now';

        if ($oldOffer) {
            if ($oldOffer->price <= $price) {
                return $this->respondError('Offer price must be higher than the previous one');
            }

            $this->offerRepository->updateOffer($oldOffer, [
                'status' => OrderStatusEnum::PENDING,
                'is_second' => true,
                'price' => $price,
                'arrival_time' => $time,
                'deleted_at' => Carbon::now()->addMinutes(self::MAX_OFFER_TIME),
            ]);
        } else {
            $orderLatitude = $order->location_latitude;
            $orderLongitude = $order->location_longitude;
            $providerLatitude = $data['latitude'];
            $providerLongitude = $data['longitude'];
            $distance = $this->calculateDistanceBetweenProviderAndOrder($providerLatitude, $providerLongitude, $orderLatitude, $orderLongitude);

            $this->offerRepository->createOffer([
                'order_id' => $orderId,
                'provider_id' => $providerId,
                'price' => $price,
                'arrival_time' => $time,
                'status' => OrderStatusEnum::PENDING,
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'distance' => $distance,
                'deleted_at' => Carbon::now()->addMinutes(self::MAX_OFFER_TIME),
            ]);
        }

        return $this->respondSuccess('Offer sent successfully');
    }

    public function calculateDistanceBetweenProviderAndOrder($providerLatitude, $providerLongitude, $orderLatitude, $orderLongitude): float
    {
        $theta = $providerLongitude - $orderLongitude;
        $dist = sin(deg2rad($providerLatitude)) * sin(deg2rad($orderLatitude)) +  cos(deg2rad($providerLatitude)) * cos(deg2rad($orderLatitude)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $kilometers = $dist * 60 * 1.1515 * 1.609344;
        return $kilometers;
    }

    private function isOrderAvailableForOffer(Order $order): bool
    {
        return $order->status === OrderStatusEnum::PENDING;
    }

    private function canProviderOfferForOrder(int $providerId, int $orderId): bool
    {
        return $this->offerRepository->countOffersForProviderOrder($providerId, $orderId) === 0;
    }

    private function canProviderSendMoreOffers(int $providerId): bool
    {
        return $this->offerRepository->countOffersByProvider($providerId, OrderStatusEnum::PENDING->value) < self::MAX_OFFERS_PER_PROVIDER;
    }

    private function canOrderAcceptMoreOffers(Order $order): bool
    {
        return $this->offerRepository->countOffersByOrder($order->id, OrderStatusEnum::PENDING->value) < self::MAX_OFFERS_PER_ORDER;
    }

    private function autoRemoveOldOffers(int $orderId = null, int $providerId = null): void
    {
        $this->offerRepository->removeOldOffers($orderId, $providerId);
    }
}
