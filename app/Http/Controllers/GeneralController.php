<?php

namespace App\Http\Controllers;

use App\Http\Traits\Helpers\ApiResponseTrait;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
use ApiResponseTrait;
    public function getSocialMediaLinks(){
        $data = [
            'facebook' => 'https://facebook.com/yourprofile',
            'twitter' => 'https://twitter.com/yourprofile',
            'instagram' => 'https://instagram.com/yourprofile',
            'whatsapp' => 'https://wa.me/yourwhatsappnumber',
            'website' => 'https://yourwebsite.com',
        ];

        return $this->respondSuccessWithData('Social media links', $data);

    }
}
