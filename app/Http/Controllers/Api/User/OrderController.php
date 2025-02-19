<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Services\UserOrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    use ApiResponseTrait;

    private $userServiceOrder;

    public function __construct(UserOrderService $userServiceOrder)
    {
        $this->userServiceOrder = $userServiceOrder;
    }

    public function getUserOrders(): JsonResponse
    {
        $user = request()->user();
        $status = request()->status;
        $orders = $this->userServiceOrder->getUserOrders($user, $status);

        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
    }

    public function getUserOrder($orderId)
    {
        $user = request()->user();
         $order = $this->userServiceOrder->getUserOrder($orderId,$user);
         if (!$order) {
             return $this->respondNotFound('Order not found');
         }
        return $this->respondWithResource(new OrderResource($order), '');
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Exception
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->userServiceOrder->createOrder($request);
        $order->load(['orderSubServices']);
        return $this->respondWithResource(new OrderResource($order), 'Order created successfully');
    }


    public function cancelOrder($orderId)
    {
        $user = request()->user();
        $order = $user->orders()->where('id', $orderId)->where('status', OrderStatusEnum::PENDING)->first();
        if (!$order) {
            return $this->respondNotFound('Order not found');
        }
         $this->userServiceOrder->cancelOrder($order);

        return $this->respondSuccess('Order canceled successfully');
    }


}
