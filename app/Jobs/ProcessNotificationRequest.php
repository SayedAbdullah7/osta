<?php

namespace App\Jobs;

use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;
use Illuminate\Support\Carbon;

class ProcessNotificationRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $userIds,
        public array $providerIds,
        public string $title,
        public string $body,
        public array $data = []
    ) {}

    public function handle(FirebaseNotificationService $notificationService)
    {
        $notificationService->accessToken = $notificationService->generateAccessToken();
        $notificationService->executeSendNotificationToUser(
            $this->userIds,
            $this->providerIds,
            $this->title . Carbon::now()->toDateTimeString(),
            $this->body,
            $this->data
        );
    }
}
