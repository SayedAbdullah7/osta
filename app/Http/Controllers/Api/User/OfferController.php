<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\OfferStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\UserOfferService;

class OfferController extends Controller
{
//    use ApiResponseTrait;
    private $userOfferService;

    public function __construct(UserOfferService $userOfferService)
    {
        $this->userOfferService = $userOfferService;
    }

    public function index($orderId)
    {
        $user = auth()->user();
        return $this->userOfferService->getOffersForOrder($orderId,$user);
    }

    public function acceptOffer($offerId)
    {
        $user = auth()->user();
        return $this->userOfferService->acceptOffer($offerId,$user);
    }

    public function rejectOffer($offerId)
    {
        return $this->userOfferService->rejectOffer($offerId);
    }

//
//    /**
//     * Display a listing of the resource.
//     */
//    public function index($orderId, Request $request)
//    {
//        $user = $request->user();
//        $order = $user->orders()->where('id', $orderId)->first();
//        if ($order) {
//            $offers = $order->offers()->get();
//            return $this->respondWithResource(OfferResource::collection($offers));
//        }
//        return $this->respondNotFound();
//    }
//
//    // Function to handle accepting an offer
//    public function acceptOffer($offerId, Request $request): \Illuminate\Http\JsonResponse
//    {
//        $user = $request->user();
//
//        // Find the offer and mark it as accepted
//        $offer = $user->orders()->pending()->where('offers.id', $offerId)->where('offers.status', OfferStatusEnum::PENDING)->first();
//
//        if (!$offer) {
//            return $this->respondError('Offer not found');
//        }
//
//        DB::transaction(static function () use ($offer) {
//            $offer->status = OfferStatusEnum::ACCEPTED;
//            $offer->save();
//
//            $offerId = $offer->id;
//            $order = $offer->order;
//            $orderId = $order->id;
//            $providerId = $offer->provider_id;
//
//            // Delete other pending offers for the same order
//            Offer::where('order_id', $orderId)
//                ->where('id', '!=', $offerId)
////                ->pending()
//                ->delete();
//
//            // Delete other pending offers from the same provider in any order
//            Offer::where('provider_id', $providerId)
//                ->where('id', '!=', $offerId)
////                ->pending()
//                ->delete();
//        });
//
//        return response()->json(['message' => 'Offer accepted successfully.']);
//    }
//
//
//    /**
//     * Display the specified resource.
//     */
//    public function rejectOffer($offerId, Request $request): \Illuminate\Http\JsonResponse
//    {
//        $user = $request->user();
//        $offer = $user->orders()->pending()->where('offers.id', $offerId)->where('offers.status', OfferStatusEnum::PENDING)->first();
//        if (!$offer) {
//            return $this->respondError('Offer not found');
//        }
//
//        $offer->status = OfferStatusEnum::REJECTED;
//        $offer->save();
//        return response()->json(['message' => 'Offer rejected successfully.']);
//    }
//
//    /**
//     * Show the form for editing the specified resource.
//     */
//    public function edit(Offer $offer)
//    {
//        //
//    }
//
//    /**
//     * Update the specified resource in storage.
//     */
//    public function update(Request $request, Offer $offer)
//    {
//        //
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     */
//    public function destroy(Offer $offer)
//    {
//        //
//    }
}
