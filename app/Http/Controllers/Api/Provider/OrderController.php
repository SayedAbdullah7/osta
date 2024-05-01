<?php

namespace App\Http\Controllers\Api\Provider;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderWarrantyEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\LocationResource;
use App\Http\Resources\OrderResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Country;
use App\Models\Location;
use App\Models\Order;
use App\Models\Provider;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

class OrderController extends Controller
{
    use ApiResponseTrait;

    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }


    public function getPendingOrders(Request $request): \Illuminate\Http\JsonResponse
    {
        $provider = $request->user();
        $orders = $this->orderRepository->getAvailablePaginatedPendingOrdersForProvider($request->all(), $provider);

        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
    }


    /**
     * Retrieve and return a paginated list of orders associated with the authenticated provider.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProviderOrders(Request $request): JsonResponse
    {
        $provider = $request->user();

        $orders = $this->orderRepository->getProviderOrders($provider);

        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
    }

    /**
     * Display the specified resource.
     */
    public function remove($orderId): JsonResponse
    {
        $provider = request()->user();
        $order = $this->orderRepository->find($orderId);

        if ($order && $this->orderRepository->isAvailableToBeRemovedByProvider($order)) {
            $order->cancellationProviders()->attach($provider->id);
            return $this->respondSuccess('Order canceled successfully');
        }

        return $this->respondNotFound();
    }




//    public function accept($orderId): JsonResponse
//    {
//        $provider = request()->user();
//        $order = $this->orderRepository->find($orderId);
//
//        $order = Order::availableToAccept()->where('id', $orderId)->first();
//        if ($order && $order->isAvailableToAccept()) {
//        if ($order && $this->orderRepository->isAvailableToBeCanceledByProvider($order)) {
//
//                $order->provider_id = $provider->id;
//            $order->save();
//            return $this->respondSuccess('Order accepted successfully');
//        }
//        return $this->respondNotFound();
//    }

    /**
     * Mark the order as comig.
     */
    public function updateOrderToComing($orderId): JsonResponse
    {
        $provider = request()->user();
//        $order = Order::where('id', $orderId)
//            ->where('provider_id', $provider->id)
//            ->first();
        $order = $this->orderRepository->find($orderId);

        if ($order && $this->orderRepository->isOrderAvailableToBeComingByProvider($order, $provider)) {
            $this->orderRepository->updateOrderToComing($order);
            return $this->respondSuccess('Order updated successfully');
        }

        return $this->respondNotFound();
    }


    /**
     * Mark the order as alomst done.
     */
    public function updateOrderToAlmostDone($orderId): JsonResponse
    {
        $provider = request()->user();
        $order = $this->orderRepository->find($orderId);
        if ($order && $this->orderRepository->isOrderAvailableToBeAlmostDoneByProvider($order, $provider)) {
            $this->orderRepository->updateOrderToAlmostDone($order);
            return $this->respondSuccess('Order almost done successfully');
        }

        return $this->respondNotFound();
    }


    /**
     * Mark the order as done.
     */
    public function updateOrderToDone($orderId): JsonResponse
    {
        $provider = request()->user();
        $order = $this->orderRepository->find($orderId);

        if ($order && $this->orderRepository->isOrderAvailableToBeDoneByProvider($order, $provider)) {
            $this->orderRepository->updateOrderToDone($order);
            return $this->respondSuccess('Order done successfully');
        }

        return $this->respondNotFound();
    }


}
