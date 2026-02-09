<?php

namespace App\Http\Requests;

use App\Enums\OrderCategoryEnum;
use App\Enums\OrderWarrantyEnum;
use App\Models\SubService;
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
            'unknown_problem' => 'boolean',
            'category' => ['required', Rule::enum(OrderCategoryEnum::class)],
            'warranty_id' => ['nullable', Rule::enum(OrderWarrantyEnum::class)],
            'desc' => 'max:255',
            'service_id' => 'required|exists:services,id',
            'location_latitude' => [
                'required_without_all:location_id',
                'numeric',
            ],
            'location_longitude' => [
                'required_without_all:location_id',
                'numeric',
            ],
            'location_desc' => 'nullable|max:255',
            'location_name' => 'nullable|max:255',
            'location_id' => [
                'required_if:location_latitude,null',
                'required_if:location_longitude,null',
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
            'sub_services_ids' => [
                'array',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && $this->input('service_id')) {
                        $invalidIds = SubService::whereIn('id', $value)
                            ->where('service_id', '!=', $this->input('service_id'))
                            ->pluck('id')
                            ->toArray();
                        if (!empty($invalidIds)) {
                            $fail('The selected sub-services do not belong to the specified service.');
                        }
                    }
                },
            ],
            'sub_services_ids.*' => 'exists:sub_services,id',
            'sub_service_quantities' => 'array',
            'sub_service_quantities.*' => 'required|integer|min:1',
            'spaces_ids' => 'nullable|array',
            'spaces_ids.*' => 'nullable|exists:spaces,id',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5048',
            'voice_desc' => [
                'file',
                'max:5120',
            ],

        ];

    }
}
