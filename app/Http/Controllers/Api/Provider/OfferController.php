<?php

namespace App\Http\Controllers\Api\Provider;

use App\Enums\OrderCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\SendOfferRequest;
use App\Services\ProviderOfferService;

class OfferController extends Controller
{
//    use ApiResponseTrait;
    private $providerOfferService;

    public function __construct(ProviderOfferService $providerOfferService)
    {
        $this->providerOfferService = $providerOfferService;
    }

    public function index()
    {
        $providerId = auth()->id();
        return $this->providerOfferService->getOffersForProvider($providerId);
    }

    public function sendOffer(SendOfferRequest $request): \Illuminate\Http\JsonResponse
    {
//        $request->validated();
//        $orderId = $data['order_id'];
//        $providerId = $data['provider_id'];
//        $price = $data['price'];
        $data = $request->validated();
        // $data = $request->all();
        $data['provider_id'] = auth('provider')->id();
//        $distance =  $this->providerOfferService->getDistance($data);
        return $this->providerOfferService->sendOfferFromProvider(($data));
    }

//    /**
//     * Display a listing of the resource.
//     */
//    public function index(Request $request)
//    {
//        $offers = $request->user()->offers()->get();
//        return $this->respondWithResource(OfferResource::collection($offers));
//    }
//
//
//    /**
//     * Store a newly created resource in storage.
//     */
//    public function sendOffer($orderId, Request $request)
//    {
//        $order = Order::find($orderId);
//        if (!$order || !$order->isAvailableToSendOffer()) {
//            return $this->respondNotFound();
//        }
//        $service = $order->service->service;
//        $minPrice = $service->min_price;
//        $maxPrice = $service->max_price;
//        if (!$minPrice){
//            $maxPrice = 1;
//        }
//
//        $request->validate([
//            'price' => 'required|numeric|min:'.$minPrice.($maxPrice)?'|max:'.$maxPrice:'',
//        ]);
//
//        $provider = request()->user();
//        $providerId = $provider->id;
//
//        // Check if the provider has already order is not done (in progress)
//        $existingOrder = $provider->orders()->whereNot('status', OrderStatusEnum::DONE)->first();
//        if ($existingOrder) {
//            return $this->respondError('Cannot send an offer while an order is in progress');
//        }
//
//
//        // Check if the provider can send an offer for the given order
//        if (!$this->canProviderSendOffer($providerId, $orderId)) {
//            return $this->respondError('Cannot send more than one offer for the same order');
//        }
//
//        // Check if the order can have more offers
//        if (!$this->canOrderHaveMoreOffers($orderId)) {
//            return $this->respondError('Maximum number of offers for the order reached');
//        }
//
//        $oldOffer = Offer::where('provider_id', $providerId)
//            ->where('order_id', $orderId)->where('status', OfferStatusEnum::REJECTED)->where('is_second', false)
//            ->first();
//        if ($oldOffer){
//            if($oldOffer->price <= $request->price){
//                return $this->respondError('Offer price must be higher than the previous one');
//            }
//            $oldOffer->status = OfferStatusEnum::PENDING;
//            $oldOffer->is_second = true;
//            $oldOffer->price = $request->price;
//            $oldOffer->save();
//        }
//
//        $order->offers()->create([
//            'price' => $request->price,
//            'provider_id' => $provider->id,
//        ]);
//
//        return $this->respondSuccess('Offer sent successfully');
//    }
//
//    private function canProviderSendOffer($providerId, $orderId): bool
//    {
////        if ($order->offers()->where('provider_id', $provider->id)->exists()) {
////            return $this->respondError('You have already sent an offer');
////        }
//        $existingOffer = Offer::where('provider_id', $providerId)
//            ->where('order_id', $orderId)
//            ->where(function ($query) {
//                $query->where('status', OfferStatusEnum::PENDING)
//                    ->orWhere(function ($query2) {
//                        $query2->where('status', OfferStatusEnum::REJECTED)
//                            ->where('is_second', true);
//                    });
//            })
//            ->count();
//
//        return $existingOffer === 0;
//    }
//
//    // Function to check if the order can have more offers
//    private function canOrderHaveMoreOffers($orderId): bool
//    {
//        $pendingOffersCount = Offer::where('order_id', $orderId)
//            ->where('status', OfferStatusEnum::PENDING)
//            ->count();
//
//        return $pendingOffersCount < 7;
//    }

}
