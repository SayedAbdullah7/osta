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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * UserOfferService
 *
 * IMPORTANT FOR AI - Accepting Offers:
 * ====================================
 *
 * CRITICAL: In updateOrderWithAcceptedOffer():
 * - If offer->price = 0 (preview order): Set order->price = Setting::getPreviewCost() directly
 * - If offer->price > 0: Call $order->calculatePrice()
 *
 * See Order model class-level documentation for preview orders business logic.
 */
class UserOfferService
{
    use ApiResponseTrait;

    private $messageService;
    private $socketService;
    private $walletService;

    public function __construct(MessageService $messageService, SocketService $socketService, WalletService $walletService)
    {
        $this->messageService = $messageService;
        $this->socketService = $socketService;
        $this->walletService = $walletService;
    }

    /**
     * Get offers for a specific order.
     *
     * @param int $orderId
     * @param User $user
     * @return JsonResponse
     */
    public function getOffersForOrder(int $orderId, User $user): JsonResponse
    {
        $offers = $this->getPendingOffersForOrder($orderId, $user);
        return $this->respondWithResource(OfferResource::collection($offers));
    }


    public function getOffers($user): JsonResponse
    {
//        $offers = Offer::whereHas('order', function ($query) use ($user) {
//            $query->where('user_id', $user->id)->pending();
//        })->get();
        $offers = $this->getPendingOffersForUser($user);
//        $offers = $user->orders()->pending()->where('offers.status', OrderStatusEnum::PENDING)->get();

        return $this->respondWithResource(OfferResource::collection($offers));
    }


    /**
     * Accept an offer.
     *
     * @param int $offerId
     * @param User $user
     * @return JsonResponse
     */
    public function acceptOffer(int $offerId, User $user): JsonResponse
    {
        $offer = $this->findPendingOffer($offerId, $user);
        if (!$offer) {
            return $this->respondNotFound('Offer not found or not pending');
        }

        DB::transaction(function () use ($offer) {
            $this->processOfferAcceptance($offer);
        });

        return $this->respondSuccess('Offer accepted successfully');
    }

    /**
     * Retrieves pending offers for a specific order belonging to a user.
     *
     * @param int $orderId
     * @param User $user
     * @return Collection
     */
    private function getPendingOffersForOrder(int $orderId, User $user): Collection
    {
        return Offer::with(['provider' => function ($query) {
            $query->withCount(['orders' => function ($query2) {
                $query2->where('status', OrderStatusEnum::DONE);
            }]);
        }])->whereHas('order', function ($query) use ($user, $orderId) {
            $query->where('user_id', $user->id)
                ->where('id', $orderId);
        })->where('status', OfferStatusEnum::PENDING)->get();
    }

    /**
     * Reject an offer.
     *
     * @param int $offerId
     * @param User $user
     * @return JsonResponse
     */
    public function rejectOffer(int $offerId, User $user): JsonResponse
    {
        $offer = $this->findPendingOffer($offerId, $user);
        if (!$offer) {
            return $this->respondNotFound('Offer not found or not pending');
        }

        $offer->update(['status' => OfferStatusEnum::REJECTED]);
        $offer->fresh();
        $this->pushRejectedOfferToSocket($offer);

        return $this->respondSuccess('Offer rejected successfully');
    }

    /**
     * Retrieves pending offers for a user.
     *
     * @param User $user
     * @return Collection
     */
    private function getPendingOffersForUser(User $user): Collection
    {
        return Offer::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)->pending();
        })->pending()->get();
    }

    /**
     * Finds a pending offer by ID for a specific user.
     *
     * @param int $offerId
     * @param User $user
     * @return Offer|null
     */
    private function findPendingOffer(int $offerId, User $user): ?Offer
    {
        return Offer::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id', $offerId)
            ->where('status', OfferStatusEnum::PENDING)
            ->first();
    }

    /**
     * Processes the acceptance of an offer.
     *
     * @param Offer $offer
     * @return void
     */
    private function processOfferAcceptance(Offer $offer): void
    {
        $this->updateOfferToAccepted($offer);
        $this->deleteOtherOffers($offer);
        $this->updateOrderWithAcceptedOffer($offer);
        $this->createOrderConversation($offer);
        $this->pushAcceptedOfferToSocket($offer);
        $this->pushAcceptedOfferToFirebase($offer);
    }

    /**
     * Updates offer status to accepted.
     *
     * @param Offer $offer
     * @return void
     */
    private function updateOfferToAccepted(Offer $offer): void
    {
        $offer->update(['status' => OfferStatusEnum::ACCEPTED,'deleted_at' => null]);
    }

    /**
     * Deletes other offers related to the same order and provider.
     *
     * @param Offer $offer
     * @return void
     */
    private function deleteOtherOffers(Offer $offer): void
    {
        $this->deleteOtherOffersForOrder($offer->order_id, $offer->id);
        $this->deleteOtherOffersForProvider($offer->provider_id, $offer->id,$offer->order_id);
    }

    private function deleteOtherOffersForOrder(int $orderId, int $acceptedOfferId): void
    {
        // Eager load the order and offers
        $order = Order::with(['offers' => function ($query) use ($acceptedOfferId) {
            $query->where('id', '!=', $acceptedOfferId);
        }])->findOrFail($orderId);

        // Check if there are any offers to process
        if ($order->offers->isEmpty()) {
            return; // No offers to delete
        }

        $offers = $order->offers;
        $userId = $order->user_id;

        // Collect IDs of offers to delete
        $offerIdsToDelete = $offers->pluck('id')->toArray();

        // Push deleted offer notification to providers
        foreach ($offers as $offer) {
            $this->socketService->push(
                'provider',
                new OfferResource($offer),
                [$offer->provider_id],
                'offer_deleted',
                "Offer deleted #" . $offer->id
            );
        }

//        // Batch push notification to user for multiple deleted offers
//        $this->socketService->push(
//            'user',
//            OfferResource::collection($offers),
//            [$userId],
//            'offers_deleted',
//            'Multiple offers deleted'
//        );

        // Delete offers by IDs
        Offer::whereIn('id', $offerIdsToDelete)
            ->delete();
    }


    private function deleteOtherOffersForProvider(int $providerId, int $acceptedOfferId, int $acceptedOrderId): void
    {
        // Fetch offers with eager loading for related orders and only pending offers
        $offers = Offer::with('order')
            ->forProvider($providerId)
            ->where('id', '!=', $acceptedOfferId)
            ->pending() // Assuming you have a scope for pending offers
            ->get();

        // Early return if no offers found
        if ($offers->isEmpty()) {
            return;
        }


//        foreach ($offers as $offer) {
//            $order = $offer->order;
//            $offerResource = new OfferResource($offer);
//            $this->socketService->push('provider', $offerResource, [$offer->provider_id], 'offer_deleted', "Offer deleted #" . $offer->id);
//            if ($order && $order->id != $acceptedOrderId) {
//                $this->socketService->push('user',$offerResource, [$order->user_id], 'offer_deleted', "Offer deleted #" . $offer->id);
//            }
//        }
        // Collect IDs of offers to delete
        $offerIdsToDelete = $offers->pluck('id')->toArray();
        // Delete offers by IDs
        Offer::whereIn('id', $offerIdsToDelete)
            ->delete();
    }

    /**
     * Updates the order status and other details upon offer acceptance.
     * See class-level documentation for preview orders handling.
     *
     * @param Offer $offer
     * @return void
     */
    private function updateOrderWithAcceptedOffer(Offer $offer): void
    {
        $order = $offer->order;

        // Determine order price: if offer price is 0 (preview order), use preview_cost (المعيناة)
        // Otherwise, calculate full price including additional costs and purchases
        if ($offer->price == 0) {
            $orderPrice = \App\Models\Setting::getPreviewCost();
        } else {
            $order->calculatePrice();
            $orderPrice = $order->price;
        }

        // Update order in a single database operation
        $order->update([
            'status' => OrderStatusEnum::ACCEPTED,
            'provider_id' => $offer->provider_id,
            'price' => $orderPrice,
        ]);

        // CRITICAL: Refresh the model to get updated values from database
        // Without this, createInvoice would use old values (price = 0)
        $order->refresh();

        $this->walletService->createInvoice($order);
    }

    /**
     * Creates a conversation for the order if not exists.
     *
     * @param Offer $offer
     * @return void
     */
    private function createOrderConversation(Offer $offer): void
    {
        $conversation = $this->messageService->createConversationForModel($offer->order, [$offer->provider, $offer->order->user]);
        $actionMessage = $this->messageService->getWelcomeMessage();
        $senderId =$offer->provider_id;
        $senderType = get_class($offer->provider);
        $content = $actionMessage['message'];
        $options = $actionMessage['info'];
        $orderId = $offer->order->id;
         $this->messageService->applyCrearteMessage($conversation, $content, $senderId, $senderType, null, $options,$orderId);
    }

    /**
     * Push accepted offer to the socket.
     *
     * @param Offer $offer
     * @return void
     */
    private function pushAcceptedOfferToSocket(Offer $offer): void
    {
        $this->socketService->push('provider', new OfferResource($offer), [$offer->provider_id], 'offer_accepted', "Offer accepted #" . $offer->id);
    }

    private function pushRejectedOfferToSocket($offer): void
    {
        if(!$offer->is_second) {
            $this->socketService->push('provider', new OfferResource($offer), [$offer->provider_id], 'offer_rejected', "Offer rejected #" . $offer->id);

        }else{
            $this->socketService->push('provider', new OfferResource($offer), [$offer->provider_id], 'offer_deleted', "Offer deleted #" . $offer->id);
        }
    }

    /**
     * Pushes deleted offers to the socket.
     *
     * @param Collection $offers
     * @param string $target
     * @return void
     */
    private function pushDeletedOffersToSocket($offers, string $target): void
    {
        foreach ($offers as $offer) {
            $this->socketService->push($target, new OfferResource($offer), [$offer->provider_id], 'offer_deleted', "Offer deleted #" . $offer->id);
        }
    }



//    /**
//     * Delete other offers for the same order.
//     *
//     * @param int $orderId
//     * @param int $acceptedOfferId
//     * @return void
//     */
//    private function deleteOtherOffersForOrder(int $orderId, int $acceptedOfferId): void
//    {
//        $offers = Offer::where('order_id', $orderId)->where('id', '!=', $acceptedOfferId)->get();
//
//    }
//
//    /**
//     * Delete other offers for the same order.
//     *
//     * @param int $orderId
//     * @param int $acceptedOfferId
//     * @return void
//     */
//    private function deleteOtherOffersForOrder(int $orderId, int $acceptedOfferId): void
//    {
//        $offers = Offer::where('order_id', $orderId)->where('id', '!=', $acceptedOfferId)->get();
//
//        foreach ($offers as $offer) {
//            $this->socketService->push('provider', new OfferResource($offer), [$offer->provider_id], 'offer_deleted', "Offer deleted #" . $offer->id);
//        }
//
//        $this->socketService->push('user', OfferResource::collection($offers), [$offers->first()->order->user_id], 'offers_deleted', 'Multiple offers deleted');
//    }
//
//    /**
//     * Delete other offers from the same provider across different orders.
//     *
//     * @param int $providerId
//     * @param int $acceptedOfferId
//     * @return void
//     */
//    private function deleteOtherOffersForProvider(int $providerId, int $acceptedOfferId): void
//    {
//        $offers = Offer::where('provider_id', $providerId)
//            ->where('id', '!=', $acceptedOfferId)
//            ->where('status', OfferStatusEnum::PENDING)
//            ->get();
//
//        foreach ($offers as $offer) {
//            $this->socketService->push('user', new OfferResource($offer), [$offer->order->user_id], 'offer_deleted', "Offer deleted #" . $offer->id);
//        }
//
//        $this->socketService->push('provider', OfferResource::collection($offers), [$providerId], 'offers_deleted', 'Multiple offers deleted');
//    }
//
//
//    public function deleteOffersRealTime($offers)
//    {
//        $offerIds = $offers->pluck('id');
//        Offer::whereIn('id', $offerIds)->delete();
//
//        $providerIds = $offers->pluck('provider_id');
//        $userIds = $offers->pluck('provider_id');
//
////        $data = [];
////        $msg = '';
////        $event = 'delete';
////        $socketService = new SocketService();
////        $socketService->push('offers', $data, $offerIds, $event, $msg);
////        $socketService = new SocketService();
//
//    }
//
//
//    /**
//     * Update the order to accepted status.
//     *
//     * @param Order $order
//     * @param int $providerId
//     * @param float $price
//     * @return Order
//     */
//    private function updateOrderToAccepted(Order $order, int $providerId, float $price): Order
//    {
//        $order->status = OrderStatusEnum::ACCEPTED;
//        $order->provider_id = $providerId;
//        $order->price = $price;
//        $order->save();
//
//        $this->walletService->createInvoice($order);
//
//        return $order;
//    }
//
//    public function createConversationForOrder($order)
//    {
//        $conversation = $order->conversation;
//        if (!$conversation) {
//            $conversation = new Conversation();
//            $conversation->model_id = $order->id;
//            $conversation->model_type = get_class($order);
//            $conversation->type = 'order';
//            $conversation->save();
//
////            $conversation->participants()->attach($order->user_id);
//
//            $conversation->messages()->create([
//                'content' => 'Order accepted, number #' . $order->id,
////                'sender_id' => $order->user_id,
////                'sender_type' => get_class($order->provider),
//            ]);
//        }
//        return $conversation;
//    }
//
//
//    /**
//     * Push accepted offer to the socket.
//     *
//     * @param Offer $offer
//     * @return void
//     */
//    private function pushAcceptedToSocket(Offer $offer): void
//    {
//        $this->socketService->push('provider', new OfferResource($offer), [$offer->provider_id], 'offer_accepted', "Offer accepted #" . $offer->id);
//    }
//
////    public function pushDeletedOffersToSocket($offersId): void
////    {
////
////    }
    private function pushAcceptedOfferToFirebase(Offer $offer)
    {
        $firebaseService = new FirebaseNotificationService();
        $provider_id = $offer->provider_id;
        $firebaseService->sendNotificationToUser([],[],'Offer Accepted','Your offer has been accepted');
    }
}
