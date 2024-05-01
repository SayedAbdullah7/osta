<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Log;

class SocketService
{
    private const TIMEOUT = 10;
    private const ROOM_PREFIX = '.users.';
    private string $socketUrl;

    public function __construct()
    {
        $this->socketUrl = env('HTTP_SOCKET');
    }
    public function push( $roomPrefix,$data, array$users, $event, $msg = null)
    {
        if (!in_array($roomPrefix, ['user', 'provider'])) {
            throw new \InvalidArgumentException('Invalid room prefix. It should be either "users" or "providers".');
        }
        $data = $data;
        $msg = $msg;
        $event = $event;
//        $room = strtolower(env('APP_NAME')) ;
        $room = env('APP_NAME') . '.'.$roomPrefix;

        try {
            $data = [
                "room" => $room,
//                "to" => $users,
//                "to" => json_encode($users),
                "to" => implode(',', $users),
//                "to" => '{"Peter":35,"Ben":37,"Joe":43}',
                "data" => json_encode($this->data($data, $msg, $event)),
            ];
            $response = Http::post($this->socketUrl, $data);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable $error) {
            Log::error('Socket connection failed: ' . $error->getMessage());
        }
    }

    public function data($data, $msg, $event)
    {
        return [
            'event' => $event,
            'message' => $msg,
            'data' => $data
        ];
    }
}
