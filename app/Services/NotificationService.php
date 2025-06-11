<?php
namespace App\Services;

use App\Events\NotificationCreated;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Create a notification for a notifiable entity and dispatch a job to send it.
     *
     * @param Model $notifiable  (User or Provider)
     * @param  string  $title
     * @param  string  $message
     * @param  string  $type
     * @return Notification
     */
    public function createNotification(Model $notifiable, string $title, string $message, string $type): Notification
    {
        // Create the notification using polymorphic fields.
        $notification = Notification::create([
            'notifiable_id'         => $notifiable->id,
            'notifiable_type'     => get_class($notifiable),
            'title'                       => $title,
            'message'              => $message,
            'type'                     => $type,
        ]);

        // Dispatch a queued job to send the notification via socket.
//        SendNotificationJob::dispatch($notification);
        // Or, you could fire an event: event(new NotificationCreated($notification));

        return $notification;
    }

    /**
     * Get paginated notifications for a notifiable entity.
     *
     * @param  int  $notifiableId
     * @param  string  $notifiableType
     * @param  array   $filters
     * @param  int     $perPage
     * @return LengthAwarePaginator
     */
    public function getNotifiableNotifications(
        int $notifiableId,
        string $notifiableType,
        array $filters = [],
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator
    {
        $query = Notification::query()->where('notifiable_id', $notifiableId)
            ->where('notifiable_type', $notifiableType);

        if (!empty($filters['unread'])) {
            $query->unread();
        }
        // Add more filters if needed (e.g., by type or date)

        return $query->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId,int $notifiableId, string $notifiableType): bool
    {
        return Notification::where('id', $notificationId)
            ->where('notifiable_id', $notifiableId)
            ->where('notifiable_type', $notifiableType)
            ->update(['is_read' => true]);
    }

    /**
     * Mark all notifications for a notifiable entity as read.
     */
    public function markAllAsRead(int $notifiableId, string $notifiableType): int
    {
        return Notification::where('notifiable_id', $notifiableId)
            ->where('notifiable_type', $notifiableType)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get the count of unread notifications for a notifiable entity.
     */
    public function getUnreadCount(int $notifiableId, string $notifiableType): int
    {
        return Notification::where('notifiable_id', $notifiableId)
            ->where('notifiable_type', $notifiableType)
            ->where('is_read', false)
            ->count();
    }
}
