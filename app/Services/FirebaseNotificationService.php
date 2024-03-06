<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FirebaseNotificationService
{



    public function sendNotification($firebaseToken, $notice, $SERVER_API_KEY, $type = 0, $count = 1)
    {
//        $clients = User::device_token();
        $users = User::where('is_set_notification', 1)->device_token();
        $firebaseToken_users = $users->get()->groupBy('is_ios');

//        $data = [];
        $notes = '';

        foreach ($firebaseToken_users as $type => $fusers) {
//            if (!$client->is_set_notification)
//                continue;
            $firebaseToken = $fusers->pluck('token')->toArray();
            $d = $this->send($firebaseToken, $notes, env('SERVER_API_KEY'), $type, $count);

        }

    }


    function send($firebaseToken, $notice, $SERVER_API_KEY, $type = 0, $count = 1)
    {

        $msg = 'msg';


        if ($type) {
            $msg = array(
                'body' => $msg,
                'title' => 'TawasolMap',
                'vibrate' => 1,
                'sound' => 1,
                'badge' => $count,
                'customParam' => []
            );

            $data = array(
                'registration_ids' => $firebaseToken,
                'notification' => $msg,
                'priority' => 'high'
            );
        } else {
            $data = [
                "registration_ids" => $firebaseToken,
                "data" => [
                    "title" => $notice->name,
                    "message" => $msg,
                    "date" => $notice->date,
                    'badge' => $count,
                ]
            ];
        }
//        return $data;
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        return $response;
    }

}
