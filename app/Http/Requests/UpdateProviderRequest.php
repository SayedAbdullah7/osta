<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProviderRequest extends FormRequest
{
    protected const MAX_IMAGE_SIZE = 5120;

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
//        $providerId = $this->route('provider');
        $providerId = $this->user()->id;


        return [
            'email' => [
                'email',
                'max:255',
                Rule::unique('providers')->ignore($providerId),
            ],
            'first_name' => 'sometimes|string|max:15',
            'last_name' => 'sometimes|string|max:15',
//            'phone' => [
//                'nullable',
//                'string',
//                'max:15',
//                Rule::unique('providers')->ignore($providerId),
//            ],
            'gender' => 'sometimes|in:male,female',
            'is_phone_verified' => 'boolean',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'personal' => 'nullable|image|mimes:jpeg,png,jpg|max:' . self::MAX_IMAGE_SIZE,
            'front_id' => 'nullable|image|mimes:jpeg,png,jpg|max:' . self::MAX_IMAGE_SIZE,
            'back_id' => 'nullable|image|mimes:jpeg,png,jpg|max:' . self::MAX_IMAGE_SIZE,
            'certificate' => 'nullable|image|mimes:jpeg,png,jpg|max:' . self::MAX_IMAGE_SIZE,
            'service_id' => 'nullable|array|exists:services,id',
        ];
    }
}
