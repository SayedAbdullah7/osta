<?php

namespace App\Services;

use App\Enums\OfferStatusEnum;
use App\Enums\OrderCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Conversation;
use App\Models\Offer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserOfferService
{
    use ApiResponseTrait;
    private $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Get offers for a specific order.
     *
     * @param int $orderId The ID of the order.
     * @param User $user The user object.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the offers.
     */
    public function getOffersForOrder(int $orderId, User $user): \Illuminate\Http\JsonResponse
    {
//        $offers = $user->orders()->pending()->where('offers.order_id', $orderId)->where('offers.status', OrderStatusEnum::PENDING)->get();
//        $offers = Offer::whereHas('order', function ($query) use ($user) {
//            $query->where('user_id', $user->id);
//        })->where('status', OrderStatusEnum::PENDING)->first();
        $order = Order::find($orderId);
        $latitude = $order->location->latitude;
        $longitude = $order->location->longitude;
//        $offers = Offer::selectRaw("
//        *,
//        ( 6371 * acos( cos( radians(?) ) *
//        cos( radians( latitude ) )
//        * cos( radians( longitude ) - radians(?)
//        ) + sin( radians(?) ) *
//        sin( radians( latitude ) ) )
//        ) AS distance", [$latitude, $longitude, $latitude])
//            ->with(['provider' => function ($query) {
//                $query->withCount(['orders' => function ($query2) {
//                    $query2->where('status', \App\Enums\OrderStatusEnum::DONE);
//                }]);
//            }])
//            ->whereHas('order', function ($query) use ($user, $orderId) {
//                $query->where('user_id', $user->id)->where('id', $orderId);
//            })
//            ->where('status', OfferStatusEnum::PENDING)
//            ->get();

        $offers = Offer::with(['provider'=>function($query){
            $query->withCount(['orders'=>function($query2){
                $query2->where('status',\App\Enums\OrderStatusEnum::DONE);
            }]);
        }])->whereHas('order', function ($query) use ($user, $orderId) {
            $query->where('user_id', $user->id)->where('id', $orderId);
        })->where('status', OfferStatusEnum::PENDING)->get();


//        )->whereHas('order', function ($query) use ($user, $orderId) {
//            $query->where('user_id', $user->id)->where('id', $orderId);
//        })->where('status', OrderStatusEnum::PENDING)->get();


        return $this->respondWithResource(OfferResource::collection($offers));
    }


    public function getOffers($user): \Illuminate\Http\JsonResponse
    {
        $offers = $user->orders()->pending()->where('offers.status', OrderStatusEnum::PENDING)->get();

        return $this->respondWithResource(OfferResource::collection($offers));
    }


    /**
     * Accepts an offer.
     *
     * @param int $offerId The ID of the offer to accept.
     * @param mixed $user The user accepting the offer.
     * @return \Illuminate\Http\JsonResponse
     */
    public function acceptOffer($offerId, $user): \Illuminate\Http\JsonResponse
    {
//        $offer = Offer::pending()->wherefind($offerId);
        // Find the offer and mark it as accepted
//        $offer = $user->orders()->pending()->offers()->where('offers.id', $offerId)->where('offers.status', OrderStatusEnum::PENDING)->first();
        $offer = Offer::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id', $offerId)->where('status', OrderStatusEnum::PENDING)->first();


        if (!$offer) {
            return $this->respondNotFound('Offer not found or not pending');
        }

        DB::transaction(function () use ($offer) {
            $offer->status = OfferStatusEnum::ACCEPTED;
            $offer->save();

            // Delete other pending offers for the same order
            Offer::where('order_id', $offer->order_id)
                ->where('id', '!=', $offer->id)
                ->delete();

            // Delete other pending offers from the same provider in any order
            Offer::where('provider_id', $offer->provider_id)
                ->where('id', '!=', $offer->id)
                ->delete();


            // upate order to acccepted
            $order = $offer->order;
            $order = $this->updateOrderToAccepted($order, $offer->provider_id, $offer->price);
//            $this->createConversationForOrder($order);
            $this->messageService->createConversationForModel($order, 'Order accepted, number #' . $order->id);

            //chat start


        });
        return $this->respondSuccess('Offer accepted successfully');
    }

    // upate order to acccepted
    private function updateOrderToAccepted($order, $providerId, $price): Order
    {
        $order->status = OrderStatusEnum::ACCEPTED;
        $order->provider_id = $providerId;
        $order->price = $price;
        $order->save();
        return $order;
    }

    public function createConversationForOrder($order,)
    {
        $conversation = $order->conversation;
        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->model_id = $order->id;
            $conversation->model_type = get_class($order);
            $conversation->type = 'order';
            $conversation->save();

//            $conversation->participants()->attach($order->user_id);

            $conversation->messages()->create([
                'content' => 'Order accepted, number #' . $order->id,
//                'sender_id' => $order->user_id,
//                'sender_type' => get_class($order->provider),
            ]);
        }
        return $conversation;
    }

    /**
     * Rejects an offer.
     *
     * @param int $offerId The ID of the offer to reject.
     * @param User $user The user rejecting the offer.
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectOffer($offerId, $user): \Illuminate\Http\JsonResponse
    {
//        $offer = $user->orders()->pending()->where('offers.id', $offerId)->where('offers.status', OrderStatusEnum::PENDING)->first();
        $offer = Offer::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id', $offerId)->where('status', OrderStatusEnum::PENDING)->first();

        if (!$offer) {
            return $this->respondNotFound('Offer not found or not pending');
        }

        $offer->status = OrderStatusEnum::REJECTED;
        $offer->save();
        return $this->respondSuccess('Offer rejected successfully');
    }
}
