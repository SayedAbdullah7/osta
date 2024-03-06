<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Offer;
use App\Models\Order;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $offers = $request->user()->offers()->get();
        return $this->respondWithResource(OfferResource::collection($offers));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($orderId, Request $request)
    {
        $order = Order::find($orderId);
//        $order->isAvailableToAccept();
        if($order && $order->isAvailableToSendOffer()){
            $request->validate([
                'price' => 'required|numeric|min:1',
            ]);
            $provider = request()->user();
            if ($order->offers()->where('provider_id', $provider->id)->exists()) {
                return $this->respondError('You have already sent an offer');
            }

//            $provider->offers()->where('order_id', $order->id)->delete();
            $order->offers()->create([
                'price' => $request->price,
                'provider_id' => $provider->id,
            ]);
//            Offer::updateOrCreate([
//                'order_id' => $order->id,
//                'provider_id' => $provider->id,
//            ],[
//                'price' => $request->price,
//            ]);
            return $this->respondSuccess('Offer sent successfully');
        }
        return $this->respondNotFound();
    }

    /**
     * Display the specified resource.
     */
    public function show(Offer $offer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Offer $offer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Offer $offer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer)
    {
        //
    }
}
