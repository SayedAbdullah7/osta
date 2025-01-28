<?php
namespace App\Services;

use App\Enums\OfferStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Offer;
use App\Models\Order;
use App\Repositories\OfferRepository;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProviderOfferService
{
    use ApiResponseTrait;

    public const MAX_OFFERS_PER_ORDER = 7;
    public const MAX_OFFERS_PER_PROVIDER = 12;
    public const  MAX_OFFER_TIME = 2; // in minutes

    protected $offerRepository;
    protected $orderRepository;

    public function __construct(OfferRepository $offerRepository, OrderRepository $orderRepository)
    {
        $this->offerRepository = $offerRepository;
        $this->orderRepository = $orderRepository;
    }

    public function getOffersForProvider(int $providerId, int $page, int $perPage)
    {
        $offers = $this->offerRepository->getAllOffersForProviderPaginated($providerId, $page, $perPage);
        return $this->respondWithResourceCollection(OfferResource::collection($offers));
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
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

//        if (!$this->canProviderSendMoreOffers($providerId)) {
//            return $this->respondError('Maximum number of offers for the provider reached');
//        }

//        if (!$this->canOrderAcceptMoreOffers($order)) {
//            return $this->respondError('Maximum number of offers for the order reached');
//        }

        $oldOffer = $this->offerRepository->findOffer($providerId, $orderId, OfferStatusEnum::REJECTED, false);

        $time = $data['time'] ?? 'now';

        if ($oldOffer) {
            // if new offer not review
            if ($price != null && $oldOffer->price > 0 && $price > 0 && $oldOffer->price <= $price) {
                return $this->respondError('Offer price must be lower than the previous one');
            }


             $this->offerRepository->updateOffer($oldOffer, [
                'status' => OrderStatusEnum::PENDING,
                'is_second' => true,
                'price' => $price,
                'arrival_time' => $time,
                'deleted_at' => Carbon::now()->addMinutes(self::MAX_OFFER_TIME),
            ]);
            $offer = $oldOffer->fresh();
        } else {
            $orderLatitude = $order->location_latitude;
            $orderLongitude = $order->location_longitude;
            $providerLatitude = $data['latitude'];
            $providerLongitude = $data['longitude'];
            $distance = $this->calculateDistanceBetweenProviderAndOrder($providerLatitude, $providerLongitude, $orderLatitude, $orderLongitude);

            $offer = $this->offerRepository->createOffer([
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
            $this->orderRepository->offerCountIncrement($orderId);
            $offer = $offer->fresh();
        }
        $this->pushToSocket($offer);
        $this->pushToFirebase($offer);

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
        return $order->offers_count < self::MAX_OFFERS_PER_ORDER;
        return $this->offerRepository->countOffersByOrder($order->id, OrderStatusEnum::PENDING->value) < self::MAX_OFFERS_PER_ORDER;
    }

    private function autoRemoveOldOffers(int $orderId = null, int $providerId = null): void
    {
        $this->offerRepository->removeOldOffers($orderId, $providerId);
    }

    public function pushToSocket(Offer $offer): void
    {
        $offer->load(['provider','provider.reviewStatistics']);
        $socketService = new SocketService();
        $data = new OfferResource($offer);
        $event = 'offer_created';
        $msg = "There is a new offer #" . $offer->id;
        $user_id = $offer->order->user_id;
        $socketService->push('user',$data,[$user_id], $event, $msg);
    }

    public function offerCountIncrement(Order &$order): void
    {
            $order->increment('offer_count');
    }

    private function pushToFirebase(Offer $offer)
    {
        $firebaseService = new FirebaseNotificationService();
        $user_id = $offer->order->user_id;
        $firebaseService->sendNotificationToUser([$user_id],[],'New Offer','You have a new offer waiting for you');
    }
}
