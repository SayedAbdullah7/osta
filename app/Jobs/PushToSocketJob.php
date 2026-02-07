<?php

namespace App\Jobs;

use App\Services\SocketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PushToSocketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $roomPrefix;
    protected $data;
    protected $users;
    protected $event;
    protected $msg;
    protected $priority;

    public function __construct($roomPrefix, $data, $users, $event, $msg, $priority = null)
    {
        $this->roomPrefix = $roomPrefix;
        $this->data = $data;
        $this->users = $users;
        $this->event = $event;
        $this->msg = $msg;
        $this->priority = $priority;
    }

    /**
     * Execute the job.
     * @throws \JsonException
     */
    public function handle()
    {
        log_content('start PushToSocketJob');
        $socketService = new SocketService();
        $socketService->sendToSocket($this->roomPrefix, $this->data, $this->users, $this->event, $this->msg);
        log_content('end PushToSocketJob');

    }
}
