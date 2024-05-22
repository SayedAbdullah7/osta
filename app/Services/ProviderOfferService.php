<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class ProviderOfferService
{
    use ApiResponseTrait;

    /**
     * The maximum number of offers that an order can have.
     */
    public const MAX_OFFERS_PER_ORDER = 7;

    /**
     * The maximum number of offers that a provider can send.
     */

    public const MAX_OFFERS_PER_PROVIDER = 12;

    /**
     * The maximum time in minutes for an offer to be valid.
     */
    public const MaxOfferTime = 30;


    /**
     * Get all offers for a specific provider.
     *
     * @param int $providerId The ID of the provider.
     * @return JsonResponse A JSON response containing the offers.
     */
    public function getOffersForProvider(int $providerId): JsonResponse
    {
        $offers = Offer::with(['order','order.user','order.service','order.subServices'])->where('provider_id', $providerId)->get();


        // You may transform the offers if needed before returning them

        return $this->respondWithResource(OfferResource::collection($offers));
    }


    /**
     * Send an offer from a provider for a specific order.
     *
     * @param array $data The data of the offer, including 'order_id', 'provider_id', and 'price'.
     * @return JsonResponse A JSON response indicating the result of the operation.
     */
    public function sendOfferFromProvider(array $data): JsonResponse
    {
        $orderId = $data['order_id'];
        $providerId = $data['provider_id'];
        $price = $data['price'];

        $order = Order::find($orderId);

        // Check if the order exists and is available to send an offer
        if (!$order || !$this->isOrderAvailableForOffer($order)) {
            return $this->respondNotFound('Order not found or not available for sending an offer');
        }

        // Check if the provider can send an offer for the given order
        if (!$this->canProviderOfferForOrder($providerId, $orderId)) {
            return $this->respondError('Cannot send more than one offer for the same order');
        }

        if ($this->canProviderSendMoreOffers($providerId)) {
            return $this->respondError('Maximum number of offers for the provider reached');
        }

        // Check if the order can have more offers
        if (!$this->canOrderAcceptMoreOffers($order)) {
            return $this->respondError('Maximum number of offers for the order reached');
        }


        $oldOffer = Offer::where('provider_id', $providerId)
            ->where('order_id', $orderId)->where('status', OrderStatusEnum::REJECTED)->where('is_second', false)
            ->first();
        if (isset($data['time'])) {
            $time = $data['time'];
        }else{
            $time = 'now';
        }



        if ($oldOffer) {
            if ($oldOffer->price <= $price) {
                return $this->respondError('Offer price must be higher than the previous one');
            }

            $oldOffer->status = OrderStatusEnum::PENDING;
            $oldOffer->is_second = true;
            $oldOffer->price = $price;
            $oldOffer->arrival_time = $time;
            $oldOffer->save();


        } else {

            // calculate the distance between the provider and the order location
            // Create the offer
            $orderLatitude = $order->location->latitude;
            $orderLongitude = $order->location->longitude;
            $providerLatitude = $data['latitude'];
            $providerLongitude = $data['longitude'];
            $distance = $this->calculateDistanceBetweenProviderAndOrder($providerLatitude, $providerLongitude, $orderLatitude, $orderLongitude);

            Offer::create([
                'order_id' => $orderId,
                'provider_id' => $providerId,
                'price' => $price,
                'arrival_time' => $time,
                'status' => OrderStatusEnum::PENDING,
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
                'distance' => $distance,
            ]);
        }

        return $this->respondSuccess('Offer sent successfully');
    }
// getDistance in km
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
    /**
     * Check if a provider can send an offer for a specific order.
     *
     * @param int $providerId The ID of the provider.
     * @param int $orderId The ID of the order.
     * @return bool True if the provider can send an offer, false otherwise.
     */
    private function canProviderOfferForOrder(int $providerId, int $orderId): bool
    {
        return $this->getProviderOfferCount($providerId, $orderId) === 0;
    }


    private function canProviderSendMoreOffers(int $providerId): bool
    {
        return Offer::where('provider_id', $providerId)
                ->where('status', OrderStatusEnum::PENDING)
                ->count() < self::MAX_OFFERS_PER_PROVIDER;
    }




    /**
     * Check if an order can have more offers.
     *
     * @param Order $order The order to check.
     * @return bool True if the order can have more offers, false otherwise.
     */
    private function canOrderAcceptMoreOffers(Order $order): bool
    {
        return $order->offers()->where('status', OrderStatusEnum::PENDING)->count() < self::MAX_OFFERS_PER_ORDER;
    }
    /**
     * Get the count of offers a provider has sent for a specific order.
     *
     * @param int $providerId The ID of the provider.
     * @param int $orderId The ID of the order.
     * @return int The count of offers.
     */
    private function getProviderOfferCount(int $providerId, int $orderId): int
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

    private function autoRemoveOldOffers(int $orderId = null, int $providerId = null): void
    {
        Offer::when($orderId, function ($query) use ($orderId) {
            $query->where('order_id', $orderId);
        })->when($providerId, function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        })->where('status', OrderStatusEnum::PENDING)
            ->where('created_at', '<', now()->subMinutes(self::MaxOfferTime))->delete();
    }

}
