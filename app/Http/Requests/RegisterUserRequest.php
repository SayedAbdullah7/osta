<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['nullable','email', 'max:255', Rule::unique('users')->where(function ($query) {
                return $query->where('is_phone_verified', 1); //use scope
            }),],
            'phone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('is_phone_verified', 1); //use scope
                }),
            ],
//            'country_id' => 'exists:countries,id',
            'gender' => 'required|in:male,female',
            'personal' => 'image|mimes:jpeg,png,jpg|max:5120',
            'date_of_birth' => 'nullable|date|before:today|date_format:Y-m-d',
        ];
    }
}
