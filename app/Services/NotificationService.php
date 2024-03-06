<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Send notifications to one or more FCM tokens.
     *
     * @param array|string $fcmTokens
     * @param string $title
     * @param string $message
     * @return string
     */
    public static function sendNotification(array|string $fcmTokens, string $title, string $message)
    {
        $authKey = 'AAAAFwRRSB8:APA91bFxbzfo4Cfd0bIrn3YNu77It4mnrljU79QxiBcihFRcUqqbRrOFPSmONxbBD6xvUZLhFMbAwtnfNZ9ZdpptKUleZxLLoqfmHaqa_XEaHFddlgED_Xsc1BIZvJ-j-K3CEsvX4Eeb';

//        $fcmTokens = DeviceToken::
        $fcmTokens = 'el950rllROSaTZtpSJMBeh:APA91bF52Vi-r6SWgdl6sNkIMxHhy-PDF23adZnpOyOUlTUXHB7t3b0C1b96LyE3uk7JqVvCWXyC5JdP5ui0NpRUmpCh8rirRhOakcLquOaxuvn6JDMZtBlWqAHNx75wbYd75uel5nbP';

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $authKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => is_array($fcmTokens) ? $fcmTokens : [$fcmTokens],
            'notification' => [
                'title' => $title,
                'body' => $message,
            ],
        ]);

        return $response->body();
    }

    /**
     * Get the FCM configuration.
     *
     * @return \App\Models\Configuration
     */
    protected static function getConfiguration()
    {

//        return Configuration::where('key', 'fcmToken')->first();
    }

}
