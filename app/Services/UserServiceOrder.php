<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class UserServiceOrder
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
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

    public function createOrder($request)
    {
        $user = $request->user();

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            if (isset($validatedData['location_id']) && $validatedData['location_id']) {
                $location = $this->orderRepository->getLocationById($validatedData['location_id']);
                $array = [
                    'location_latitude' => $location->latitude,
                    'location_longitude' => $location->longitude,
                    'location_desc' => $location->desc
                ];
                $validatedData = array_merge($validatedData, $array);
            }

            $order = $this->orderRepository->createOrderBeLongToUser($validatedData, $user);

            if ($request->has('sub_services_ids') && $request->has('sub_service_quantities')) {
                $subServices = $request->input('sub_services_ids');
                $quantities = $request->input('sub_service_quantities');

                $pivotData = array_combine($subServices, array_map(static function ($quantity) {
                    return ['quantity' => $quantity];
                }, $quantities));

                $order = $this->orderRepository->attachSubServicesToOrder($pivotData, $order);
                $order = $this->orderRepository->setMaxPriceForOrder($order);
            }

            if ($request->hasFile('images')) {
                $this->orderRepository->attachImagesToOrder($request->file('images'), $order);
            }

            DB::commit();

            $order = $this->orderRepository->refreshData($order);

            return $order;

        } catch (Exception $e) {
            DB::rollback();
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
