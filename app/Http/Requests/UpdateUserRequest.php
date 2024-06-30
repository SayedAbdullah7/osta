<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->user()->id;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId)->where(function ($query) {
                    return $query->where('is_phone_verified', 1);
                }),
            ],
//            'phone' => [
//                'sometimes',
//                'string',
//                'max:15',
//                Rule::unique('users')->ignore($userId)->where(function ($query) {
//                    return $query->where('is_phone_verified', 1);
//                }),
//            ],
            'country_id' => 'sometimes|numeric|exists:countries,id',
            'gender' => 'sometimes|in:male,female',
            'date_of_birth' => 'nullable|date|before:today|date_format:Y-m-d',
            'personal' => 'sometimes|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }
}
