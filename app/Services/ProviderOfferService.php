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
     * Get all offers for a specific provider.
     *
     * @param int $providerId The ID of the provider.
     * @return JsonResponse A JSON response containing the offers.
     */
    public function getOffersForProvider(int $providerId): JsonResponse
    {
        $offers = Offer::where('provider_id', $providerId)->get();


        // You may transform the offers if needed before returning them

        return $this->respondWithResource(OfferResource::collection($offers));
    }


    /**
     * Send an offer from a provider for a specific order.
     *
     * @param array $data The data of the offer, including 'order_id', 'provider_id', and 'price'.
     * @return JsonResponse A JSON response indicating the result of the operation.
     */
    public function sendOffer(array $data): JsonResponse
    {
        $orderId = $data['order_id'];
        $providerId = $data['provider_id'];
        $price = $data['price'];


        $order = Order::find($orderId);

        // Check if the order exists and is available to send an offer
        if (!$order || !$this->isAvailableToSendOffer($order)) {
            return $this->respondNotFound('Order not found or not available for sending an offer');
        }

        // Check if the provider can send an offer for the given order
        if (!$this->canProviderSendOffer($providerId, $orderId)) {
            return $this->respondError('Cannot send more than one offer for the same order');
        }

        // Check if the order can have more offers
        if (!$this->canOrderHaveMoreOffers($order)) {
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

            // Create the offer
            Offer::create([
                'order_id' => $orderId,
                'provider_id' => $providerId,
                'price' => $price,
                'arrival_time' => $time,
                'status' => OrderStatusEnum::PENDING,
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
            ]);
        }

        return $this->respondSuccess('Offer sent successfully');
    }

    private function isAvailableToSendOffer(Order $order): bool
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
    private function canProviderSendOffer(int $providerId, int $orderId): bool
    {
        return $this->getProviderOfferCount($providerId, $orderId) === 0;
    }




    /**
     * Check if an order can have more offers.
     *
     * @param Order $order The order to check.
     * @return bool True if the order can have more offers, false otherwise.
     */
    private function canOrderHaveMoreOffers(Order $order): bool
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

//    private function autoRemoveOldO

}
