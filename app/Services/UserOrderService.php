<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserOrderService
{
    protected $orderRepository;
    protected $discountService;

    public function __construct(OrderRepository $orderRepository, DiscountService $discountService)
    {
        $this->orderRepository = $orderRepository;
        $this->discountService = $discountService;
    }

    public function getUserOrders($user, $status = null)
    {
        if ($status) {
            $statuses = [$status];
            return $this->orderRepository->getOrdersForUserWithStatusIn($user, $statuses);
        } else {
            return $this->orderRepository->getOrdersForUser($user);
        }
    }

    public function getUserOrder($id, $user): ?\App\Models\Order
    {
        return $this->orderRepository->getOrderForUserById($id, $user);
    }

    /**
     * @throws Exception
     */
    public function createOrder($request): \App\Models\Order
    {
        $user = $request->user();

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Handle location data
            if (isset($validatedData['location_id']) && $validatedData['location_id']) {
                $location = $this->orderRepository->getLocationById($validatedData['location_id']);
                $array = [
                    'location_latitude' => $location->latitude,
                    'location_longitude' => $location->longitude,
                    'location_desc' => $location->desc,
                    'location_name' => $location->name
                ];
                $validatedData = array_merge($validatedData, $array);
            }

            // Create order
            $order = $this->orderRepository->createOrderBeLongToUser($validatedData, $user);

            // Handle discount code if provided
            if ($request->discount_code) {
                $discountResult = $this->discountService->applyDiscountCodeToOrder($order, $request->input('discount_code'));

                if (!$discountResult['valid']) {
                    throw ValidationException::withMessages([
                        'discount_code' => [$discountResult['message']],
                    ]);
                }
            }

            // Attach sub-services if provided
            if ($request->has('sub_services_ids') && $request->has('sub_service_quantities')) {
                $subServices = $request->input('sub_services_ids');
                $quantities = $request->input('sub_service_quantities');
                $spaces = $request->input('spaces_ids', []); // Default to empty array if not provided

                $pivotData = [];

                foreach ($subServices as $index => $subServiceId) {
                    $pivotData[$subServiceId] = [
                        'quantity' => $quantities[$index],
                        'space_id' => $spaces[$index] ?? null, // Use null if space_id is not provided
                    ];
                }

                $order = $this->orderRepository->attachSubServicesToOrder($pivotData, $order);
                $order = $this->orderRepository->setMaxPriceForOrder($order);
                $order->fresh();

                if ($order->discount_code && $order->max_allowed_price > 0) {
                    $this->discountService->updateOrderWithDiscount($order);
                }
            }

            // Attach images if provided
            if ($request->hasFile('images')) {
                $this->orderRepository->attachImagesToOrder($request->file('images'), $order);
            }

            // Attach voice description if provided
            if ($request->hasFile('voice_desc')) {
                $this->orderRepository->attachVoiceToOrder($request->file('voice_desc'), $order);
            }

            DB::commit();

            $order = $this->orderRepository->refreshData($order);

            return $order;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function pushToSocket(Order $order): void
    {
        $order = Order::withRelationsInProvider()
            ->find($order->id);
        $socketService = new SocketService();
        $data = new OrderResource($order);
        $event = 'order_created';
        $msg = "There is a new order available #" . $order->id;
        $service_id = $order->service_id;
        $lastHour = Carbon::now()->subHours(24)->toDateTimeString();
        $providers = \App\Models\Provider::whereHas('tokens', function ($q) use ($lastHour) {
            $q->where('last_used_at', '>=', $lastHour);
        })
        ->whereHas('services', function ($q2) use ($service_id) {
            $q2->where('services.id', $service_id);
        })
        ->pluck('id')->toArray();
        $socketService->push('provider', $data, $providers, $event, $msg);
    }

    public function confirmOrder(Order $order): void
    {
        $order->is_confirmed = 1;
        $order->save();
    }
    public function cancelOrder(Order $order): void
    {
        $order->is_confirmed = 0;
        $order->status = OrderStatusEnum::CANCELED;
        $order->save();
    }
}
