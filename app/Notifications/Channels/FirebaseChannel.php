<?php
// app/Notifications/Channels/FirebaseChannel.php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\FirebaseNotificationService;

class FirebaseChannel implements NotificationChannelInterface
{
    protected FirebaseNotificationService $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Send the notification via Firebase using the custom FirebaseNotificationService.
     *
     * @param mixed        $notifiable   The notifiable entity.
     * @param Notification $notification The notification instance.
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toFirebase')) {
            // The notification should return an array with the necessary data.
            $firebaseData = $notification->toFirebase($notifiable);

            // Extract values from the returned array.
            $userIds     = $firebaseData['user_ids']    ?? [];
            $providerIds = $firebaseData['provider_ids']  ?? [];
            $title       = $firebaseData['title']         ?? '';
            $body        = $firebaseData['body']          ?? '';
            $data        = $firebaseData['data']          ?? [];
//            dd($userIds, $providerIds, $title, $body, $data);

            // Use your FirebaseNotificationService to send the push notification.
            $this->firebaseService->sendNotificationToUser($userIds, $providerIds, $title, $body, $data);
        }
    }
}
