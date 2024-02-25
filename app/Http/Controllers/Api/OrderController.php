<?php

namespace App\Http\Controllers\Api;

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

    /**
     * Retrieve and return a paginated list of pending orders associated with the authenticated provider.
     *
     * This method handles the logic for retrieving pending orders based on various parameters,
     * including the provider's services, location, and user-defined sorting criteria.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pending_index(Request $request): \Illuminate\Http\JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $sortBy = $request->input('sort_by', 'id');
        $sortDesc = $request->input('sort_desc', $sortBy === 'id');

        $provider = $request->user();

        $providerLongitude = $request->longitude;
        $providerLatitude = $request->latitude;
        $serviceIds = $request->service_id;

        $providerServiceIds = $provider->services()->when($serviceIds, function ($query) use ($serviceIds) {
            $query->whereIn('services.id', $serviceIds);
        })->pluck('services.id')->toArray();

        $query = Order::with('location')->pending()->whereIn('service_id', $providerServiceIds);
        $query = $this->applyOrderBy($query, $sortBy, $sortDesc, $providerLatitude, $providerLongitude);

        $orders = $query->simplePaginate($perPage);

        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
    }
    /**
     * Apply custom ordering based on user input.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param bool $sortDesc
     * @param float|null $providerLatitude
     * @param float|null $providerLongitude
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyOrderBy(\Illuminate\Database\Eloquent\Builder $query, string $sortBy, bool $sortDesc, ?float $providerLatitude, ?float $providerLongitude): \Illuminate\Database\Eloquent\Builder
    {
        return $query->when($sortBy === 'distance' && $providerLongitude && $providerLatitude, function ($query) use ($providerLatitude, $providerLongitude, $sortDesc) {
            $haversine = Order::calculateHaversineDistance($providerLatitude, $providerLongitude);
            return $query->select('orders.*', $haversine)
                ->join('locations', 'orders.location_id', '=', 'locations.id')
                ->orderBy('distance', $sortDesc ? 'desc' : 'asc');
        }, function ($query) use ($sortBy, $sortDesc) {
            return $query->orderBy($sortBy === 'time' ? 'start' : 'id', $sortDesc ? 'desc' : 'asc');
        });
    }

    public function user_orders_index()
    {
        $user = request()->user();

        $orders = $user->orders()->get();

        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Create a new order
            $order = new Order;
            $order->start = $validatedData['start'];
            $order->end = $validatedData['end'];
            $order->warranty_id = $validatedData['warranty_id'] ?? null;
            $order->desc = $validatedData['desc'];
            $order->service_id = $validatedData['service_id'];
            //$order->provider_id = $validatedData['provider_id'];
            $order->location_id = $validatedData['location_id'];

            $order->user_id = $user->id;
            $order->save();

            // Attach sub_services to the order with quantities
            if (isset($validatedData['sub_services']) && is_array($validatedData['sub_services'])) {
                foreach ($validatedData['sub_services'] as $subService) {
                    $order->subServices()->attach($subService['sub_service_id'], ['quantity' => $subService['sub_service_quantity']]);
                }
            }

            // Attach images to the order
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $media = $order->addMedia($image)->toMediaCollection('images'); // Adjust the collection name as needed
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new Exception($e);
        }

        $order->refresh();
        $order->load('subServices', 'service', 'user', 'location');
        return $this->respondWithResource(new OrderResource($order), 'order created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }


    public function storeLocation(Request $request)
    {
        $user = $request->user();

        // Validate incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'apartment_number' => 'required|string|max:255',
            'floor_number' => 'required|string|max:255',
            'latitude' => 'required|string|max:255',
            'longitude' => 'required|string|max:255',
            'desc' => 'required|string|max:255',
            'city_id' => [
                'required',
                Rule::exists('cities', 'id')->where(function ($query) use ($request, $user) {
                    // Additional condition to check city's country_id
                    $query->where('country_id', $user->country_id);
                }),
            ],
        ]);

        // Create a new location instance
        $location = new Location();

        $location->name = $validatedData['name'];
        $location->street = $validatedData['street'];
        $location->apartment_number = $validatedData['apartment_number'];
        $location->floor_number = $validatedData['floor_number'];
        $location->latitude = $validatedData['latitude'];
        $location->longitude = $validatedData['longitude'];
        $location->desc = $validatedData['desc'];
        $location->is_default = (bool)$request->is_default;
        $location->city_id = $validatedData['city_id'];
        $location->user_id = $user->id;

        // Save the location
        $location->save();
        if ($location->isDefault()) {
            $user->locations()->default()->where('id', '!=', $location->id)->update(['is_default' => 0]);
        }
        return $this->respondWithResource(new LocationResource($location), 'location created');

    }
}
