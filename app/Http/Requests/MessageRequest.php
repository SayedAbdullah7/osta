<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
//            'content' => 'required|string',
            'conversation_id' => 'required_without:order_id|exists:conversations,id',
            'order_id' => 'required_without:conversation_id|exists:orders,id'
        ];
    }
}
