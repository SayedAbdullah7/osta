<?php

// app/Services/ProviderOrderService.php
namespace App\Services;


use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;

class ProviderOrderService
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;

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
        } elseif ($request->payment_method == 'wallet'){
            $payResponse =  (new \App\Services\WalletService())->payByWallet($order);
        }

        if (!$payResponse['status']) {
            return ['status' => false, 'message' => $payResponse['message']];
        }

        $this->orderRepository->updateOrderToDone($order);

        return ['status' => true, 'message' => 'Order done successfully'];
    }

}
