<?php

namespace App\Http\Controllers\Api\User;
use App\Http\Controllers\Controller;
use App\Services\DiscountService;
use Illuminate\Http\Request;

class DiscountCodeController extends Controller
{
    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }



    /**
     * Check the validity of a discount code.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkValidity(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $result = $this->discountService->checkDiscountCodeValidity($request->code);

        if ($result['success']) {
            return response()->json(['message' => $result['message']]);
        }

        return response()->json(['error' => $result['error']], 400);
    }

    /**
     * Store a newly created discount code.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->discountService->createDiscountCode($request->all());

        if ($result['success']) {
            return response()->json($result['discount_code'], 201);
        }

        return response()->json($result['errors'], 400);
    }

    /**
     * Apply a discount code to an order.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric',
            'user_id' => 'required|integer',
        ]);

        $result = $this->discountService->applyDiscountCode($request->code, $request->amount);

        if ($result['success']) {
            // Deactivate the discount code after applying it
            $this->discountService->deactivateDiscountCode($request->code, $request->user_id);
            return response()->json($result);
        }

        return response()->json(['error' => $result['error']], 400);
    }
}

