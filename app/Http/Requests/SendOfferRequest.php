<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $order = \App\Models\Order::find($this->order_id);
        $priceRules = ['required', 'numeric', 'min:0'];
        if ($order && $order->unknown_problem == 1) {
            $priceRules[] = function ($attribute, $value, $fail) {
                if ($value != 0 && $value != null) {
//                    $fail('The price must be 0 or null when unknown problem is true.');
                    $fail('Cant send offer with price for order with unknown problem.');
                }
            };
        } else {
            $priceRules[] = function ($attribute, $value, $fail) use ($order) {
                $maxAllowedPrice = $order->max_allowed_price ?? 0;
                if ($maxAllowedPrice > 0  && $value > $maxAllowedPrice) {
                    $fail('The price must be less than or equal to ' . $maxAllowedPrice);
                }
            };
        }

        return [
            'order_id' => 'required',
            'latitude' => 'required','max:15',
            'longitude' => 'required','max:15',
            'time' => 'required',
            'price' => $priceRules,
        ];
//        return [
////             'order_id' => 'required|exists:orders,id',
//            'order_id' => 'required',
//            'latitude' => 'required','max:15',
//            'longitude' => 'required','max:15',
//            'price' => [
////                'required', 'numeric', 'min:0',
//                'required', 'numeric', 'min:0',
//                function ($attribute, $value, $fail) {
//                    $order = \App\Models\Order::find($this->order_id);
//                    if ($order && $value > ($max = $order->maxAllowedOfferPrice())) {
//                        $fail('The price must be less than or equal to ' . $max);
//                    }
//                },
//            ],
//        ];
    }
}
