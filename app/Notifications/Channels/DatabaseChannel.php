<?php

namespace App\Notifications\Channels;

use App\Services\NotificationService;
use Illuminate\Notifications\Notification;

class DatabaseChannel implements NotificationChannelInterface
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toDatabase')) {
            $data = $notification->toDatabase($notifiable);

            // Expecting data keys: title, message, and type.
            $title   = $data['title']   ?? 'Notification';
            $message = $data['message'] ?? '';
            $type    = $data['type']    ?? 'system';
//            dd($notifiable);

            // Use the NotificationService to create the database record.
            $this->notificationService->createNotification($notifiable, $title, $message, $type);
        }
    }
}
