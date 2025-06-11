<?php
// app/Notifications/MyCustomNotification.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
//use Kreait\Firebase\Messaging\CloudMessage;

class MyCustomNotification extends Notification
{
    protected string $messageText;
    protected $model;

    public function __construct(string $messageText,$model = null)
    {
        $this->messageText = $messageText;
        $this->model = $model;
    }

    // Optionally list channels this notification supports.
    public function via($notifiable)
    {
        return ['database', 'firebase', 'socket'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => 'Order Fully Paid',
            'message' => $this->messageText,
            'type'    => 'system',
        ];
    }

    public function toFirebase($notifiable)
    {
        return [
            'user_ids'     => [$notifiable->id],
            'provider_ids' => [], // Optionally pass provider IDs if needed.
            'title'        => 'New Offer',
            'body'         => $this->messageText,
            'data'         => [
                // Include any extra data you need.
            ],
        ];
    }

    public function toSocket($notifiable)
    {
        // Prepare payload for SocketService.
        // Adjust the keys as needed. For example, if the notifiable is a provider:
        return [
            'roomPrefix' => 'provider', // room prefix; e.g. "provider" or "user" etc.
            'data'       => $this->model, // the data to send to the socket.
            'users'      => [$notifiable->id], // or any array of user identifiers you need to notify.
            'event'      => 'custom_socket_event', // your event name, e.g., 'invoice_paid_by_user'
            'msg'        => $this->messageText, // optional, can be a more detailed message.
        ];
    }
}
