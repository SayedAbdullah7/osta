<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;

interface NotificationChannelInterface
{
    /**
     * Send the given notification.
     *
     * @param mixed        $notifiable   The model (or models) that should receive the notification.
     * @param Notification $notification The notification instance.
     */
    public function send($notifiable, Notification $notification);
}
