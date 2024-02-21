<?php

namespace App\Http\Controllers\Api;

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
        $sortDesc = $request->input('sort_desc', true);

        $provider = $request->user();

        $providerLongitude = $request->longitude;
        $providerLatitude = $request->latitude;
        $order_by = $request->order_by;
        $serviceIds = $request->service_id;

        $providerServiceIds = $provider->services()->when($serviceIds, function ($query) use ($serviceIds) {
            $query->whereIn('services.id', $serviceIds);
        })->pluck('services.id')->toArray();

        $query = Order::with('location')->pending()->whereIn('service_id', $providerServiceIds);
        $query = $this->applyOrderBy($query, $order_by, $providerLatitude, $providerLongitude);

        $orders = $query->simplePaginate($perPage);

        return $this->respondWithResourceCollection(OrderResource::collection($orders), '');
    }
    /**
     * Apply custom ordering based on user input.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $order_by
     * @param float|null $providerLatitude
     * @param float|null $providerLongitude
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyOrderBy(\Illuminate\Database\Eloquent\Builder $query, string $order_by, ?float $providerLatitude, ?float $providerLongitude): \Illuminate\Database\Eloquent\Builder
    {
        return $query->when($order_by === 'distance' && $providerLongitude && $providerLatitude, function ($query) use ($providerLatitude, $providerLongitude) {
            $haversine = Order::calculateHaversineDistance($providerLatitude, $providerLongitude);
            return $query->select('orders.*', $haversine)
                ->join('locations', 'orders.location_id', '=', 'locations.id')
                ->orderBy('distance');
        }, function ($query) use ($order_by) {
            return $query->orderBy($order_by === 'time' ? 'start' : 'id');
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
        // Validate the request data
        $validatedData = $request->validate([
            'start' => 'required|date_format:Y-m-d H:i',
            'end' => 'nullable|date_format:Y-m-d H:i',
            'warranty' => 'nullable|in:lev1,lev2,lev3',
            'status' => 'nullable|in:pending,accepted,coming,almost done,done',
            'desc' => 'required|string|max:255',
//            'price' => 'required|numeric|min:1',
//            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
//            'location_id' => 'required|exists:locations,id',
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where(function ($query) use ($request, $user) {
                    $query->where('user_id', $user->id);
                }),
            ],
//            'sub_services' => 'array',
            'sub_services.*.sub_service_id' => 'required|exists:sub_services,id',
            'sub_services.*.sub_service_quantity' => 'required|integer`|min:1`',
            'sub_services' => function ($attribute, $value, $fail) {
                // Custom validation rule to check if sub_service_id and quantity have the same number of entries
                $subServiceIds = array_column($value, 'sub_service_id');
                $quantities = array_column($value, 'sub_service_quantity');

                if (count($subServiceIds) !== count($quantities)) {
                    $fail('The number of sub_service_id entries must match the number of sub_service_quantity entries.');
                }
            },
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // Adjust the allowed file types and size
        ]);
        $user = $request->user();
        try {
            DB::beginTransaction();
            // Create a new order
            $order = new Order;
            $order->start = $validatedData['start'];
            $order->end = $validatedData['end'];
            $order->warranty = $validatedData['warranty'];
            $order->desc = $validatedData['desc'];
            $order->user_id = $user->id;
            $order->service_id = $validatedData['service_id'];
            //$order->provider_id = $validatedData['provider_id'];
            $order->location_id = $validatedData['location_id'];
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
