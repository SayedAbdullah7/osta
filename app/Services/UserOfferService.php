<?php

namespace App\Services;

use App\Enums\OfferStatusEnum;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class UserOfferService
{
    use ApiResponseTrait;

    public function getOffersForOrder($orderId, $user)
    {
        $offers = $user->orders()->pending()->where('offers.order_id', $orderId)->where('offers.status', OfferStatusEnum::PENDING)->get();

        return $this->respondWithResource(OfferResource::collection($offers));
    }
    public function getOffers($user): \Illuminate\Http\JsonResponse
    {
        $offers = $user->orders()->pending()->where('offers.status', OfferStatusEnum::PENDING)->get();

        return $this->respondWithResource(OfferResource::collection($offers));
    }

    public function acceptOffer($offerId, $user): \Illuminate\Http\JsonResponse
    {
//        $offer = Offer::pending()->wherefind($offerId);
        // Find the offer and mark it as accepted
        $offer = $user->orders()->pending()->where('offers.id', $offerId)->where('offers.status', OfferStatusEnum::PENDING)->first();

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
        });
        return $this->respondSuccess('Offer accepted successfully');
    }

    public function rejectOffer($offerId,$user): \Illuminate\Http\JsonResponse
    {
        $offer = $user->orders()->pending()->where('offers.id', $offerId)->where('offers.status', OfferStatusEnum::PENDING)->first();

        if (!$offer) {
            return $this->respondNotFound('Offer not found or not pending');
        }

        $offer->status = OfferStatusEnum::REJECTED;
        $offer->save();
        return $this->respondSuccess('Offer rejected successfully');
    }
}
