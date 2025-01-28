<?php

namespace App\Services;

use App\Models\DeviceToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
class FirebaseNotificationService
{
    public string $url;
    public string $accessToken;

    public function __construct()
    {
        $this->url = config('custom.fcm_endpoint');
        $this->accessToken = $this->generateAccessToken();
    }

    /**
     * Generate access token for Firebase Cloud Messaging.
     *
     * @return string|null
     */
    private function generateAccessToken()
    {
        // Check if the token exists in cache
        if (Cache::has('firebase_access_token')) {
            return Cache::get('firebase_access_token');
        }
        try {
            // Path to the service_account.json file
            $credentialsFilePath = storage_path('app/private/service_account.json');
            // Create credentials object
            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                $credentialsFilePath
            );
            // Fetch the token
            $token = $credentials->fetchAuthToken();
            $accessToken = $token['access_token'];
            // Cache the token for 55 minutes
            Cache::put('firebase_access_token', $accessToken, now()->addMinutes(55));
            return $accessToken;
        } catch (\Exception $e) {
            Log::error('Error generating access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send push notifications via Firebase Cloud Messaging.
     *
     * @param $to
     * @param string $title
     * @param string $body
     */
    public function sendPushNotificationSync($to, $title, $body)
    {
        // Generate access token for Firebase
        $access_token = $this->generateAccessToken();
        // Retrieve the user's device details
//        $devices = DeviceToken::where('user_id', $to->id)
//            ->orderBy('created_at', 'DESC')
//            ->get();
        $devices = $to->get();

        // Define the FCM endpoint
        $fcmEndpoint = $this->url;
        foreach ($devices as $device) {
            if (!empty($device)) {

                try {
                    // Prepare the message payload (title and body only)
                    $message = [
                        'message' => [
                            'token' => $device->token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body
                            ]
                        ]
                    ];
                    // Send the notification via HTTP POST request
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $access_token,
                        'Content-Type' => 'application/json',
                    ])->post($fcmEndpoint, $message);
                    // Log the result of the notification
                    if ($response->status() == 200) {
                        Log::info('Notification sent successfully: ' . $response->body());
                    } else {
                        Log::error('Error sending FCM notification: ' . $response->body());
                    }
                    return $response->json();
                } catch (\Exception $e) {
                    return $e;
                    Log::error('Error sending FCM notification: ' . $e->getMessage());
                }
            }
        }
    }


    /**
     * Send a notification to a specific device token.
     *
     * @param string $deviceToken
     * @param string $title
     * @param string $body
     * @param array $data
     * @param bool $isIos
     * @return array
     */
    public function sendNotification(string $deviceToken, string $title, string $body, array $data = [], bool $isIos = false)
    {
        $url = $this->url;

        // Common payload structure
        $payload = [
            'token' => $deviceToken,
//            'priority' => 'high',
//            'data' => $data,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        // Platform-specific modifications
        if ($isIos) {
//            $payload['notification']['sound'] = 'default';
//            $payload['notification']['badge'] = 1;
//            $payload['notification']['content_available'] = true;
        } else {
//            $payload['android'] = [
//                'priority' => 'HIGH',
//            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ])->post($url, ['message' => $payload]);

        return $response->json();
    }

    /**
     * Send notification to all devices of users and providers.
     *
     * @param array $userIds
     * @param array $providerIds
     * @param string $title
     * @param string $body
     * @param array $data
     * @return void
     */
    public function sendNotificationToUser(array $userIds = [], array $providerIds = [], string $title, string $body, array $data = [])
    {
        $deviceTokens = DeviceToken::query()
//            ->when(!empty($userIds), fn($query) => $query->whereIn('user_id', $userIds))
//            ->when(!empty($providerIds), fn($query) => $query->whereIn('provider_id', $providerIds))
            ->when(!empty($userIds) || !empty($providerIds), function ($query) use ($userIds, $providerIds) {
                // Apply an OR condition for user_id and provider_id
                $query->where(function ($subQuery) use ($userIds, $providerIds) {
                    // If userIds is provided, add whereIn condition for user_id
                    if (!empty($userIds)) {
                        $subQuery->whereIn('user_id', $userIds);
                    }
                    // If providerIds is provided, add orWhereIn condition for provider_id
                    if (!empty($providerIds)) {
                        $subQuery->orWhereIn('provider_id', $providerIds);
                    }
                });
            })
            ->where('is_set_notification', true)
            ->select('token', 'is_ios')
            ->get();

        // Group tokens by platform
        $tokensByPlatform = $deviceTokens->groupBy('is_ios');

        foreach ($tokensByPlatform as $isIos => $tokens) {
            foreach ($tokens->pluck('token') as $token) {
               $this->sendNotification($token, $title, $body, $data, (bool)$isIos);
            }
        }
    }

    public function setNotification(bool $isSetNotification, $user): void
    {
        $user->deviceTokens()->update(['is_set_notification' => $isSetNotification]);
    }
}
