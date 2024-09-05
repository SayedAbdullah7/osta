<?php

// app/Services/ProviderOrderService.php
namespace App\Services;


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

        if (!$order || !$this->orderRepository->isOrderAvailableToBeDoneByProvider($order, $provider)) {
            return null;
        }

        if ($request->payment_method == 'cash') {
            $payResponse = (new \App\Services\WalletService())->payCash($order);
        } elseif ($request->payment_method == 'wallet') {
            $payResponse = (new \App\Services\WalletService())->payByWallet($order);
        }

        if (!$payResponse['status']) {
            return ['status' => false, 'message' => $payResponse['message']];
        }

        $this->orderRepository->updateOrderToDone($order);
        $this->providerStatisticsService->handleOrderCompletion($providerId);

        return ['status' => true, 'message' => 'Order done successfully'];
    }

    public function updateOrderPrice($order, $newPrice)
    {
//        $order = $this->orderRepository->find($orderId);
        return DB::transaction(function () use ($order, $newPrice) {
            $order->price = $newPrice;
            $walletService = new WalletService();
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
