<?php

namespace App\Listeners;

use App\Events\OrderPaidByUserEvent;
use App\Http\Resources\InvoiceResource;
use App\Services\NotificationService;
use App\Services\SocketService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderPaidByUserListener
{
    protected NotificationService $notificationService;
    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaidByUserEvent $event)
    {
        $order = $event->order;
        $user = $event->user;
        $provider = $order->provider;
        $invoice = $order->invoice; // Assuming there's an invoice relation

        $this->notificationService->createNotification(
            $provider,
            'Order Fully Paid',
            "Client {$user->name} has fully paid Order #{$order->id}.",
            'system',
        );


        $socketService = new SocketService();
        $data = new InvoiceResource($invoice);
        $eventName = 'invoice_paid_by_user';
        $msg = "User {$user->name} has fully paid Order #{$order->id}.";
        $provider_id = $order->provider_id;

        // Push event to the provider
        $socketService->push('provider', $data, [$provider_id], $eventName, $msg);
    }
}
