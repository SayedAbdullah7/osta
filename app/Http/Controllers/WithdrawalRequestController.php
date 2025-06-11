<?php

namespace App\Http\Controllers;

use App\Http\Requests\WithdrawalRequestStoreRequest;
use App\Http\Resources\WithdrawalRequestResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalRequestController extends Controller
{
    use ApiResponseTrait;

    protected $withdrawalService;

    public function __construct(WithdrawalRequestService $withdrawalService)
    {
        $this->withdrawalService = $withdrawalService;
    }

    public function requestWithdrawal(WithdrawalRequestStoreRequest $request): JsonResponse
    {
        try {
            $provider = Auth::user(); // Ensure the provider is authenticated
            $withdrawal = $this->withdrawalService->requestWithdrawal(
                $provider,
                $request->amount,
                $request->payment_method,
                $request->payment_details
            );

        return $this->respondSuccess('Withdrawal request submitted successfully.');
            return response()->json([
                'message' => 'Withdrawal request submitted successfully.',
                'data' => $withdrawal
            ]);
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    /**
     * Display a paginated list of withdrawal requests for the authenticated provider.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        // Get the authenticated provider
        $provider = Auth::user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 10);

        // Fetch withdrawal requests for the provider with pagination (e.g., 10 per page)
        $withdrawals = WithdrawalRequest::where('provider_id', $provider->id)
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        // Return paginated resource collection
//        return WithdrawalRequestResource::collection($withdrawals);
        return $this->respondWithResourceCollection(WithdrawalRequestResource::collection($withdrawals));
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WithdrawalRequest $withdrawalRequest)
    {
        //
    }
}
