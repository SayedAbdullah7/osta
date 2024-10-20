<?php

// app/Services/ProviderOrderService.php
namespace App\Services;


use App\Http\Resources\OfferResource;
use App\Http\Resources\OrderResource;
use App\Models\Offer;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\ProviderStatisticService;
class ProviderOrderService
{
    private OrderRepositoryInterface $orderRepository;
    protected \App\Services\ProviderStatisticService $providerStatisticsService;

    public function __construct(OrderRepositoryInterface $orderRepository, ProviderStatisticService $providerStatisticsService)
    {
        $this->orderRepository = $orderRepository;
        $this->providerStatisticsService = $providerStatisticsService;
    }


    public function updateOrderToComing($orderId, $provider): ?\App\Models\Order
    {
        $order = $this->orderRepository->find($orderId);
        if ($order && $this->orderRepository->isOrderAvailableToBeComingByProvider($order, $provider)) {
            return $this->orderRepository->updateOrderToComing($order);
        }
        return null;
    }

    public function updateOrderToAlmostDone($orderId, $provider): ?\App\Models\Order
    {
        $order = $this->orderRepository->find($orderId);
        if ($order && $this->orderRepository->isOrderAvailableToBeAlmostDoneByProvider($order, $provider)) {
            return $this->orderRepository->updateOrderToAlmostDone($order);
        }
        return null;
    }

    public function updateOrderToDone(Request $request, $orderId, $provider): ?array
    {
        $order = $this->orderRepository->find($orderId);
        $providerId = $provider->id;
        if (!$order || !$this->orderRepository->isOrderAvailableToBeDoneByProvider($order, $provider)) {
            return null;
        }

        if ($request->payment_method == 'cash') {
            $payResponse = app(WalletService::class)->payCash($order);
        } elseif ($request->payment_method == 'wallet') {
            $payResponse = app(WalletService::class)->payByWallet($order);
        }

        if (!$payResponse['status']) {
            return ['status' => false, 'message' => $payResponse['message']];
        }

        $this->orderRepository->updateOrderToDone($order);
        $this->providerStatisticsService->handleOrderCompletion($providerId);
//        $this->
        $this->pushToSocket($order);


        return ['status' => true, 'message' => 'Order done successfully'];
    }
    public function pushToSocket(Order $order): void
    {
        $socketService = new SocketService();
        $data = new OrderResource($order);
        $event = 'order_done';
        $msg = "Order done with id {$order->id}";
        $user_id = $order->user_id;
        $socketService->push('user',$data,[$user_id], $event, $msg);
        $socketService->push('provider',$data,[$order->provider_id], $event, $msg);
    }

    public function updateOrderPrice($order, $newPrice)
    {
//        $order = $this->orderRepository->find($orderId);
        return DB::transaction(function () use ($order, $newPrice) {
            $order->price = $newPrice;
            $walletService = app(WalletService::class);
            $invoice = $order->invoice;
            if (!$invoice) {
                $walletService->createInvoice($order);
            }
//            if ($invoice->payment_status == 'paid') {
//                $this->notFoundException('this action is not available');
//            }
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
            $order->save();
            return $order;
        });
    }

    private function notFoundException($message): void
    {
        throw new NotFoundHttpException($message);
    }

}
