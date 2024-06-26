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
            'content' => 'required_without:media',
            'conversation_id' => 'required_without:order_id|exists:conversations,id',
            'order_id' => 'required_without:conversation_id|exists:orders,id',
//            'media' => 'required'
            'media.*' => 'file'
//            'media.*' => 'file|mimes:jpeg,jpg,png,mp4,mov,ogg|max:6000', // max 6MB
        ];
    }
}
