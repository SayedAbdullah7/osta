<?php

namespace App\Http\Controllers\Api\User;

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

    public function getUserOrders()
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
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->userServiceOrder->createOrder($request);
        return $this->respondWithResource(new OrderResource($order), 'Order created successfully');
    }
}
