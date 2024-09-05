<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\DiscountService;
use App\Services\WalletService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($orderId)
    {
//        $invoice = Invoice::where('order_id', $orderId)->first();
        $order = Order::where('id', $orderId)->where('status', '!=', \App\Enums\OrderStatusEnum::PENDING)->firstOrFail();
//        $walletService = new WalletService();
        $walletService = app(WalletService::class);

        $invoice = $order->invoice ?? $walletService->createInvoice($order);
        $invoice->load('order.subServices');
        return $this->respondWithResource(new InvoiceResource($invoice));
//        return $this->respondSuccess()->additional([
//            'data' => new InvoiceResource($invoice),
//        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAdditionalCost(Request $request, $orderId)
    {
        $order = Order::where('id', $orderId)->where('status', '!=', \App\Enums\OrderStatusEnum::PENDING)->firstOrFail();
//        $discountService = new DiscountService();
//                $walletService = new WalletService($discountService);

        $walletService = app(WalletService::class);
        $invoice = $order->invoice ?? $walletService->createInvoice($order);
        $additionalCost = $request->get('additional_cost');
        if ($additionalCost > 30) {
            return $this->respondError('Additional cost can not be greater than 30');
        }
        if ($additionalCost < 1) {
            return $this->respondError('Additional cost can not be less than 1');
        }
        $order->price = $order->price + $additionalCost;
        $order->save();
        $walletService = new WalletService();
        $walletService->updateInvoiceAdditionalCost($invoice, $order);
        return $this->respondSuccess()->additional([
            'data' => new InvoiceResource($invoice),
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
