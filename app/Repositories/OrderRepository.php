<?php

namespace App\Repositories;

use App\Enums\OrderCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Location;
use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderRepository implements OrderRepositoryInterface
{
//    public function __construct(OrderRepositoryInterface $orderRepository)
//    {
//        $this->orderRepository = $orderRepository;
//    }

    public function getOrdersForUser(User $user): Collection
    {
        return $user->orders()->withCount('offers')->with(['service','subServices'])->get();
    }
    public function getOrdersForUserWithStatusIn(mixed $user, array $statuses)
    {
        return $user->orders()->withCount('offers')->with(['service','subServices'])->whereIn('status', $statuses)->get();
    }
    public function find(int $id)
    {
        return Order::find($id);
        return Order::findOrFail($id);
    }
    // find order that belongs to provider
//    public function findOrderBelongToProvider(int $id, Provider $provider): Order
//    {
//        return $provider->orders()->findOrFail($id);
//    }

    /**
     * Retrieve and return a paginated list of pending orders associated with the authenticated provider.
     *
     * This method handles the logic for retrieving pending orders based on various parameters,
     * including the provider's services, location, and user-defined sorting criteria.
     *
     * @param array $params
     * @param $provider
     * @return Paginator
     */
    public function getAvailablePaginatedPendingOrdersForProvider(array $params, $provider): Paginator
    {
        $perPage = $params['per_page'] ?? 20;
        $sortBy = $params['sort_by'] ?? 'id';
        $sortDesc = $params['sort_desc'] ?? ($sortBy === 'id');

        $providerId = $provider->id;

        $providerLongitude = $params['longitude'] ?? null;
        $providerLatitude = $params['latitude'] ?? null;
        $serviceIds = $params['service_id'] ?? null;

        $providerServiceIds = $provider->services()->when($serviceIds, function ($query) use ($serviceIds) {
            $query->whereIn('services.id', $serviceIds);
        })->pluck('services.id')->toArray();

        $query = Order::with('location','subServices','service')->pending()->whereIn('service_id', $providerServiceIds)->whereDoesntHave('cancellationProviders', function ($query) use ($providerId) {
            $query->where('provider_id', $providerId);
        });
        $query = $this->applyOrderBy($query, $sortBy, $sortDesc, $providerLatitude, $providerLongitude);

        return $query->simplePaginate($perPage);
    }
    /**
     * Apply custom ordering based on user input.
     *
     * @param Builder $query
     * @param string $sortBy
     * @param bool $sortDesc
     * @param float|null $providerLatitude
     * @param float|null $providerLongitude
     * @return Builder
     */
    private function applyOrderBy(Builder $query, string $sortBy, bool $sortDesc, ?float $providerLatitude, ?float $providerLongitude): Builder
    {
        return $query->when($sortBy === 'distance' && $providerLongitude && $providerLatitude, function ($query) use ($providerLatitude, $providerLongitude, $sortDesc) {
            $haversine = Order::calculateHaversineDistance($providerLatitude, $providerLongitude);
            return $query->select('orders.*', $haversine)
//                ->join('locations', 'orders.location_id', '=', 'locations.id')
                ->orderBy('distance', $sortDesc ? 'desc' : 'asc');
        }, function ($query) use ($sortBy, $sortDesc) {
            return $query->orderBy($sortBy === 'time' ? 'start' : 'id', $sortDesc ? 'desc' : 'asc');
        });
    }

    //get orders that belongs to provider
    public function getProviderOrders(Provider $provider): Collection
    {
        return $provider->orders()->get();
    }

//    public function getPendingOrders(int $perPage, string $sortBy, bool $sortDesc, array $serviceIds, int $providerId, ?float $providerLatitude, ?float $providerLongitude): Collection
//    {
//        $query = Order::with('location')->pending()->whereIn('service_id', $serviceIds)->whereDoesntHave('cancellationProviders', function ($query) use ($providerId) {
//            $query->where('provider_id', $providerId);
//        });
//        $query = $this->applyOrderBy($query, $sortBy, $sortDesc, $providerLatitude, $providerLongitude);
//
//        return $query->simplePaginate($perPage);
//    }
//
//    private function applyOrderBy($query, string $sortBy, bool $sortDesc, ?float $providerLatitude, ?float $providerLongitude)
//    {
//        return $query->when($sortBy === 'distance' && $providerLongitude && $providerLatitude, function ($query) use ($providerLatitude, $providerLongitude, $sortDesc) {
//            $haversine = Order::calculateHaversineDistance($providerLatitude, $providerLongitude);
//            return $query->select('orders.*', $haversine)
//                ->join('locations', 'orders.location_id', '=', 'locations.id')
//                ->orderBy('distance', $sortDesc ? 'desc' : 'asc');
//        }, function ($query) use ($sortBy, $sortDesc) {
//            return $query->orderBy($sortBy === 'time' ? 'start' : 'id', $sortDesc ? 'desc' : 'asc');
//        });
//    }

    public function isAvailableToBeRemovedByProvider (Order $order): bool
    {
        return !$order->provider_id && $order->status === OrderStatusEnum::PENDING;
    }

    public function isOrderAvailableToBeComingByProvider(Order $order,Provider $provider): bool
    {
        return $order->status === OrderStatusEnum::ACCEPTED && $this->isOrderBelongToProvider($order,$provider);
    }

    public function isOrderAvailableToBeAlmostDoneByProvider(Order $order,Provider $provider): bool
    {
        return $order->status === OrderStatusEnum::COMING && $this->isOrderBelongToProvider($order,$provider);
    }

    public function isOrderAvailableToBeDoneByProvider(Order $order,Provider $provider): bool
    {

        return $this->isOrderBelongToProvider($order,$provider);
//        return $order->status === OrderStatusEnum::ALMOST_DONE && $this->isOrderBelongToProvider($order,$provider);
    }

    public function updateOrderToComing($order): Order
    {
        $order->status = OrderStatusEnum::COMING;
        return $order->save();
    }

    public function updateOrderToAlmostDone($order): Order
    {
        $order->status = OrderStatusEnum::ALMOST_DONE;
        return $order->save();
    }

    public function updateOrderToDone($order): Order
    {
        $order->status = OrderStatusEnum::DONE;
        $order->save();
        return $order;
    }

    public function isOrderBelongToProvider(Order $order,Provider $provider): bool
    {
        return $order->provider_id === $provider->id;
    }

    public function cancelOrder(int $orderId, int $providerId): bool
    {
        $order = Order::find($orderId);
        if ($order && $order->isAvailableToCancel()) {
            $order->cancellationProviders()->attach($providerId);
            return true;
        }
        return false;
    }

    public function acceptOrder(int $orderId, int $providerId): bool
    {
        $order = Order::availableToAccept()->where('id', $orderId)->first();
        if ($order && $order->isAvailableToAccept()) {
            $order->provider_id = $providerId;
            $order->save();
            return true;
        }
        return false;
    }

    public function updateOrderStatus(int $orderId, int $providerId, string $status): bool
    {
        $order = Order::where('id', $orderId)->where('provider_id', $providerId)->first();
        if ($order) {
            $order->status = $status;
            $order->save();
            return true;
        }
        return false;
    }


    // Implement the logic to get orders for a user
//    public function getOrdersForUser(User $user): Collection
//    {
//        return $user->orders()->get();
//    }

    // Create a new order
    // create order belong to user
    public function createOrderBeLongToUser(array $data, User $user): Order
    {
        $order = new Order;
        $order->start = $data['start']??null;
        $order->end = $data['end']??null;
        $order->category = $data['category'];
        $order->space = $data['space']??null;
        $order->unknown_problem = $data['unknown_problem']??false;
        if ($data['category'] != OrderCategoryEnum::Other->value) {
            $order->warranty_id = $data['warranty_id'] ?? null;
        }
        $order->desc = $data['desc']??null;
        $order->service_id = $data['service_id'];
        //$order->provider_id = $data['provider_id'];
//        $order->location_id = $data['location_id']??null;

        $order->user_id = $user->id;
        $order->location_latitude = $data['location_latitude'];
        $order->location_longitude = $data['location_longitude'];
        $order->location_desc = $data['location_desc'];
        $order->save();

        return $order;
    }

    public function attachSubServicesToOrder(array $data, Order $order): Order
    {
        $order->subServices()->attach($data);
        return $order;
    }

    public function attachImagesToOrder(array $data, Order $order): Order
    {
        foreach ($data as $image) {
            $media = $order->addMedia($image)->toMediaCollection('images'); // Adjust the collection name as needed
        }
        return $order;
    }

    public function setMaxPriceForOrder(Order $order): Order
    {
        $order->max_allowed_price = $order->maxAllowedOfferPrice();
        $order->save();
        return $order;
    }

    public function refreshData(Order $order): Order
    {
        $order->refresh();
        return $order;
    }

    public function loadRelations(Order $order): Order
    {
        $order->load('user', 'location', 'warranty', 'service', 'subServices');
        return $order;
    }


    public function store(array $data, User $user): Order
    {

        try {
            DB::beginTransaction();

            // Create a new order
            $order = new Order;
            $order->start = $data['start'];
            $order->end = $data['end'];
            $order->warranty_id = $data['warranty_id'] ?? null;
            $order->desc = $data['desc'];
            $order->service_id = $data['service_id'];
            $order->location_id = $data['location_id'];
            $order->user_id = $user->id;
            $order->save();

            // Attach sub-services to the order with quantities
            if (isset($data['sub_services_ids']) && isset($data['sub_service_quantities'])) {
                $subServices = $data['sub_services_ids'];
                $quantities = $data['sub_service_quantities'];
//                Log::info($quantities);
                // store sub services and quantities in json file by Storge

                // Ensure the number of sub-services and quantities match
                if (count($subServices) === count($quantities)) {
                    $order->subServices()->attach(array_combine($subServices, $quantities));
                } else {

                    // Handle mismatch error as needed
//                    throw new Exception('Sub-services and quantities must match.');
                }
            }

            // Attach images to the order
            if (isset($data['images'])) {
                foreach ($data['images'] as $image) {
                    $media = $order->addMedia($image)->toMediaCollection('images'); // Adjust the collection name as needed
                }
            }

            DB::commit();
            $order->refresh();
            $order->load('subServices', 'service', 'user', 'location');
            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            throw new Exception($e);
        }
    }

    public function getLocationById( $location_id)
    {
        return Location::find($location_id);
    }



}
