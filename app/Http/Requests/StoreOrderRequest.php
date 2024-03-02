<?php

namespace App\Http\Requests;

use App\Enums\OrderWarrantyEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreOrderRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'start' => 'required|date_format:Y-m-d H:i',
            'end' => 'nullable|date_format:Y-m-d H:i',
            'warranty_id' => [Rule::enum(OrderWarrantyEnum::class)],
            'desc' => 'required|string|max:255',
            'service_id' => 'required|exists:services,id',
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }),
            ],
            'sub_services' => [
                'array',
                function ($attribute, $value, $fail) {
                    // Custom validation rule to check if sub_services_ids and quantity have the same number of entries
                    $subServiceIds = array_column($value, 'sub_services_ids');
                    $quantities = array_column($value, 'sub_service_quantities');

                    if (count($subServiceIds) !== count($quantities)) {
                        $fail('The number of sub_services_ids entries must match the number of sub_service_quantities entries.');
                    }
                },
            ],
            'sub_services.*.sub_services_ids' => 'required|exists:sub_services,id',
            'sub_services.*.sub_service_quantities' => 'required|integer|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ];

    }
}
