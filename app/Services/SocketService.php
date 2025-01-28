<?php

namespace App\Services;

use App\Jobs\PushToSocketJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

    class SocketService
{
    private const TIMEOUT = 10;
    private const ROOM_PREFIX = '.users.';
    private string $socketUrl;

    public function     __construct()
    {
        $this->socketUrl = env('HTTP_SOCKET');
    }
        public function push($roomPrefix, $data, array $users, $event, $msg = null, $priority = null)
        {
            if (empty($roomPrefix) || empty($users) || empty($event)) {
                throw new \InvalidArgumentException('Invalid parameters for socket push.');
            }

            PushToSocketJob::dispatch($roomPrefix, $data, $users, $event, $msg)
                ->onQueue($priority ?? env('SOCKET_JOB_QUEUE', 'default'));
        }

        public function sendToSocket($roomPrefix, $data, $users, $event, $msg = null)
        {
            $room = env('APP_NAME') . '.' . $roomPrefix;

            try {
                $payload = [
                    "room" => $room,
                    "to" => implode(',', $users),
                    "data" => json_encode($this->data($data, $msg, $event)),
                ];

                Log::info('Sending data to socket', ['payload' => $payload]);
                $response = Http::timeout(self::TIMEOUT)->retry(3, 100)->post($this->socketUrl, $payload);

                if ($response->successful()) {
                    Log::info('Socket response received', ['body' => $response->body()]);
                    return $response->body();
                }

                Log::warning('Socket response failed', ['status' => $response->status(), 'body' => $response->body()]);
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
