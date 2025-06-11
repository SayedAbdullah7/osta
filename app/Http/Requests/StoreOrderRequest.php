<?php

namespace App\Http\Requests;

use App\Enums\OrderCategoryEnum;
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
            'unknown_problem' => 'boolean',
            'category' => ['required', Rule::enum(OrderCategoryEnum::class)],
//            'space' => 'required_if:category,' . OrderCategoryEnum::SpaceBased->value . '|max:15',
//            'start' => 'required|date_format:Y-m-d H:i',
//            'end' => 'nullable|date_format:Y-m-d H:i',
            'warranty_id' => ['nullable', Rule::enum(OrderWarrantyEnum::class)],
            'desc' => 'max:255',
//            'desc' => 'required_if:unknown_problem,true|max:255',
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
            'sub_services_ids' => 'array',
            'sub_services_ids.*' => 'exists:sub_services,id',
            'sub_service_quantities' => 'array',
            'sub_service_quantities.*' => 'required|integer|min:1',

//            'sub_services_ids' => 'required|array|exists:sub_services,id',
//            'sub_service_quantities.*' => 'required|integer|min:1',
//            'sub_services.*.sub_services_ids' => 'required|exists:sub_services,id',
//            'sub_services.*.sub_service_quantities' => 'required|integer|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5048',
//            'voice_desc' => 'file|mimes:audio/mpeg,audio/wav,audio/mp3|max:10240', // 10MB Max
            'voice_desc' => [
                'file',              // Validates that the input is a file
//                'mimes:mp3,wav,ogg,aac',// Specifies allowed file types
                'max:5120',         // Maximum file size in kilobytes (50 MB)
            ],

        ];

    }
}
