<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\SocketService;

class SocketChannel implements NotificationChannelInterface
{
    protected SocketService $socketService;

    public function __construct(SocketService $socketService)
    {
        $this->socketService = $socketService;
    }

    public function send($notifiable, Notification $notification)
    {
        // The notification should define a toSocket() method returning a payload array.
        if (method_exists($notification, 'toSocket')) {
            $payload = $notification->toSocket($notifiable);

            // Expect payload keys: 'roomPrefix', 'data', 'users', 'event', and optionally 'msg'
            if (!isset($payload['roomPrefix'], $payload['users'], $payload['event'])) {
                // Optionally, throw an exception or log an error.
                return;
            }

            $roomPrefix = $payload['roomPrefix'];
            $data       = $payload['data']??null;
            $users      = $payload['users'];
            $event      = $payload['event'];
            $msg        = $payload['msg'] ?? null;

            // Use your SocketService to dispatch the push job.
            $this->socketService->push($roomPrefix, $data, $users, $event, $msg);
        }
    }
}
