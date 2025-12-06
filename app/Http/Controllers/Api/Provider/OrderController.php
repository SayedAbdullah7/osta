<?php

namespace App\Http\Controllers\Api\Provider;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderWarrantyEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\LocationResource;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SubServiceResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\Country;
use App\Models\Location;
use App\Models\Order;
use App\Models\Provider;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Services\OrderService;
use App\Services\ProviderOrderService;
use App\Services\WalletService;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
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
    private $orderService;


    public function __construct(ProviderOrderService $orderService ,OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;

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

//        $orders = $this->orderRepository->getOrdersForProvider($provider);
        $provider = request()->user();
        $status = request()->status;
        if ($status) {
            $statuses = [$status];
            $orders = $this->orderRepository->getOrdersForProviderWithStatusIn($provider, $statuses);
        } else {
            $orders= $this->orderRepository->getOrdersForProvider($provider);
        }
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
//    public function updateOrderToComing($orderId): JsonResponse
//    {
//        $provider = request()->user();
////        $order = Order::where('id', $orderId)
////            ->where('provider_id', $provider->id)
////            ->first();
//        $order = $this->orderRepository->find($orderId);
//
//        if ($order && $this->orderRepository->isOrderAvailableToBeComingByProvider($order, $provider)) {
//            $this->orderRepository->updateOrderToComing($order);
//            return $this->respondSuccess('Order updated successfully');
//        }
//
//        return $this->respondNotFound();
//    }
    public function updateOrderToComing($orderId): JsonResponse
    {
        $provider = request()->user();
        $order = $this->orderService->updateOrderToComing($orderId, $provider);

        if ($order) {
            return $this->respondSuccess('Order updated successfully');
        }

        return $this->respondNotFound();
    }


    /**
     * Mark the order as alomst done.
     */
//    public function updateOrderToAlmostDone($orderId): JsonResponse
//    {
//        $provider = request()->user();
//        $order = $this->orderRepository->find($orderId);
//        if ($order && $this->orderRepository->isOrderAvailableToBeAlmostDoneByProvider($order, $provider)) {
//            $this->orderRepository->updateOrderToAlmostDone($order);
//            return $this->respondSuccess('Order almost done successfully');
//        }
//
//        return $this->respondNotFound();
//    }
    public function updateOrderToAlmostDone($orderId): JsonResponse
    {
        $provider = request()->user();
        $order = $this->orderService->updateOrderToAlmostDone($orderId, $provider);

        if ($order) {
            return $this->respondSuccess('Order almost done successfully');
        }

        return $this->respondNotFound();
    }


    /**
     * Mark the order as done.
     * @throws ExceptionInterface
     */
//    public function updateOrderToDone(Request $request, $orderId)
//    {
//        $request->validate([
//            'payment_method' => ['required', Rule::in(['cash', 'wallet'])],
//        ]);
//        $provider = request()->user();
//        $order = $this->orderRepository->find($orderId);
//
//
//        if (!$order || !$this->orderRepository->isOrderAvailableToBeDoneByProvider($order, $provider)) {
//            return $this->respondNotFound();
//        }
////        DB::transaction(function () use ($order) {
//            if ($request->payment_method == 'cash') {
//                return  $payReesponse = (new \App\Services\WalletService())->payCash($order);
//            } elseif ($request->payment_method == 'wallet'){
//                $payReesponse =  (new \App\Services\WalletService())->payByWallet($order);
//            }
//
//            if (!$payReesponse['status']) {
//                return $this->respondError($payReesponse['message']);
//            }
//            $this->orderRepository->updateOrderToDone($order);
//
//            return $this->respondSuccess('Order done successfully');
//    }

    public function updateOrderToDone(Request $request, $orderId): JsonResponse
    {
//        $request->validate([
//            'payment_method' => ['required', Rule::in(['cash', 'wallet'])],
//        ]);

        $provider = request()->user();
        $response = $this->orderService->updateOrderToDone($request, $orderId, $provider);

        if (!$response) {
            return $this->respondNotFound();
        }

        if (!$response['status']) {
            return $this->respondError($response['message']);
        }

        return $this->respondSuccess($response['message']);
    }

    public function getOrderDetails(Request $request,$orderId): \Illuminate\Http\JsonResponse
    {
        $order = auth()->user()->orders()->where('id', $orderId)->first();

        if (!$order) {
            return $this->respondNotFound();
        }
        $orderDetails = $order->orderDetails;
        $walletService = app(WalletService::class);
        $invoice = $order->invoice;
        if (!$invoice) {
            $invoice = $walletService->createInvoice($order);
        }
        return $this->apiResponse([
            'success' => true,
            'result' => [
                'details' => OrderDetailResource::collection($orderDetails),
                'invoice' => InvoiceResource::collection([$invoice]),
            ],
            'message' => '',
        ], 200);
        return $this->respondWithResource(OrderDetailResource::collection($orderDetails), '');
    }

}
