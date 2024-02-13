<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:15',
            'last_name' => 'required|string|max:15',
            'phone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('providers')->where(function ($query) {
                    return $query->where('is_phone_verified', 1);
                }),
            ],
            'is_phone_verified' => 'boolean',
            'password' => 'required|string|confirmed|min:8',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'personal' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'front_id' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'back_id' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'certificate' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'service_id' => 'required|array|exists:services,id',
            'bank_account_name' => 'required|string|max:80',
            'bank_account_iban' => 'required|string|max:35',
        ];
    }
}
