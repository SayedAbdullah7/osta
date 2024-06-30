<?php

namespace App\Http\Controllers\Api\User;

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
use App\Repositories\OrderRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\OrderCategoryEnum;
class OrderController extends Controller
{
    use ApiResponseTrait;

    private $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

//    public function user_orders_index(): JsonResponse
    public function getUserOrders(): JsonResponse
    {
        $user = request()->user();
        $status = \request()->status;
        if ($status){
            $statuses = [$status];
            $orders = $this->orderRepository->getOrdersForUserWithStatusIn($user,$statuses);
        }else{
            $orders = $this->orderRepository->getOrdersForUser($user);
        }
        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
//        $user = request()->user();
//        $orders = $user->orders()->get();
//        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
//        $user = $request->user();
//        $validatedData = $request->validated();
//        $order = $this->orderRepository->store($validatedData, $user);
//        return $this->respondWithResource(new OrderResource($order), 'order created successfully');
//        try {
////            $order = $this->orderRepository->store($validatedData, $user);
//        } catch (\Exception $e) {
////            return $this->respondError($e->getMessage());
//        }

//        $user = $request->user();
//        $validatedData = $request->validated();
//        return $this->orderRepository->storeOrder($validatedData, $user);

        $user = $request->user();
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            if(isset($validatedData['location_id']) && $validatedData['location_id'] ){
                $location = $this->orderRepository->getLocationById($validatedData['location_id']);
                $array =[
                    'location_latitude'=> $location->latitude,
                    'location_longitude' => $location->longitude,
                    'location_desc'=>$location->desc
                ];
                $validatedData = array_merge($validatedData,$array);
            }

            $order = $this->orderRepository->createOrderBeLongToUser($validatedData, $user);

            // Attach sub-services to the order with quantities
//            if ($request->has('sub_services_ids') && $request->has('sub_service_quantities') && $request->input('category') != OrderCategoryEnum::Other->value)  {
            if ($request->has('sub_services_ids') && $request->has('sub_service_quantities'))  {

                $subServices = $request->input('sub_services_ids');
                $quantities = $request->input('sub_service_quantities');

                $pivotData = array_combine($subServices, array_map(static function ($quantity) {
                    return ['quantity' => $quantity];
                }, $quantities));

                $order = $this->orderRepository->attachSubServicesToOrder($pivotData, $order);

                $order =$this->orderRepository->setMaxPriceForOrder($order);

            }
            // Attach images to the order
            if ($request->hasFile('images')) {
                $this->orderRepository->attachImagesToOrder($request->file('images'), $order);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new Exception($e);
        }

        $order = $this->orderRepository->refreshData($order);
//        $order = $this->loadRelations($order);
//        $this
        return $this->respondWithResource(new OrderResource($order), 'order created successfully');
    }



}
