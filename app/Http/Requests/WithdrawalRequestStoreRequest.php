<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequestStoreRequest extends FormRequest
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
        return [
            'amount'          => 'required|numeric|min:100', // Example: Minimum withdrawal amount
            'payment_method'  => 'required|string|in:bank_transfer,paypal,e-wallet',
            'payment_details' => 'required|array',
            'payment_details.bank_name' => 'required_if:payment_method,bank_transfer|string',
            'payment_details.account_number' => 'required_if:payment_method,bank_transfer|string',
            'payment_details.account_name' => 'required_if:payment_method,bank_transfer|string',
            'payment_details.mobile_number' => 'required_if:payment_method,e-wallet|string',
            'payment_details.mobile_name' => 'required_if:payment_method,e-wallet|string',
            ];
    }
}
