<?php
// app/Services/NotificationManager.php

namespace App\Services;

use App\Notifications\Channels\NotificationChannelInterface;
use Illuminate\Notifications\Notification;

class NotificationManager
{
    /**
     * An array of channel implementations.
     *
     * @var NotificationChannelInterface[]
     */
    protected $channels = [];

    /**
     * Optionally pass an array of channels on instantiation.
     */
    public function __construct(array $channels = [])
    {
        $this->channels = $channels;
    }

    /**
     * Add a channel to the manager.
     *
     * @param NotificationChannelInterface $channel
     * @return $this
     */
    public function addChannel(NotificationChannelInterface $channel)
    {
        $this->channels[] = $channel;
        return $this;
    }

    /**
     * Send the notification to the given notifiable(s) via selected channels.
     *
     * @param mixed        $notifiables   A single model or an array of models.
     * @param Notification $notification  The notification instance.
     * @param array        $channelAliases Optional list of channel aliases (class basenames) to use.
     */
    public function send($notifiables, Notification $notification, array $channelAliases = [])
    {
        // Ensure we have an array of notifiables.
        if (!is_array($notifiables)) {
            $notifiables = [$notifiables];
        }

        foreach ($this->channels as $channel) {
            // If specific channels are requested, skip others.
            if (!empty($channelAliases)) {
                $alias = class_basename($channel);
                if (!in_array($alias, $channelAliases)) {
                    continue;
                }
            }

            foreach ($notifiables as $notifiable) {
                $channel->send($notifiable, $notification);
            }
        }
    }
}
