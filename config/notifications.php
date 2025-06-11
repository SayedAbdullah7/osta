<?php
// config/notifications.php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Here you may specify the available notification channels and their
    | associated classes. You can easily enable or disable channels by
    | modifying the default_channels array.
    |
    */
    'channels' => [
        'database' => \App\Notifications\Channels\DatabaseChannel::class,
        'firebase' => \App\Notifications\Channels\FirebaseChannel::class,
        'socket'   => \App\Notifications\Channels\SocketChannel::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Channels
    |--------------------------------------------------------------------------
    |
    | List of channel keys that should be added to the notification manager by
    | default. You can override these values at runtime if needed.
    |
    */
    'default_channels' => ['database', 'firebase', 'socket'],
];
