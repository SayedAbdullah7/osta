<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required_without:media|string',
            'conversation_id' => 'required_without:order_id|exists:conversations,id',
            'order_id' => 'required_without:conversation_id|exists:orders,id',
            'media' => 'required'
//            'media' => 'sometimes|file|mimes:jpeg,jpg,bmp,png,mp4,mov,ogg,qt|max:50000', // max 5MB
        ];
    }
}
