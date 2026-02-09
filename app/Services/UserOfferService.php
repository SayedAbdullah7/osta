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
        $offers = $this->getPendingOffersForUser($user);
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
    private function pushAcceptedOfferToFirebase(Offer $offer)
    {
        $firebaseService = new FirebaseNotificationService();
        $provider_id = $offer->provider_id;
        $firebaseService->sendNotificationToUser([], [$provider_id], 'Offer Accepted', 'Your offer has been accepted');
    }
}
