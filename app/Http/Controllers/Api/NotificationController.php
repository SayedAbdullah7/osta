<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use  apiResponseTrait;
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get paginated notifications with optional filtering.
     */
    public function index(Request $request)
    {
        // Determine the authenticated notifiable entity.
        // For example, if you have separate guards:
        // $notifiable = auth('user')->user() ?? auth('provider')->user();
        $notifiable = auth()->user(); // Adjust as needed.

//        if (!$notifiable) {
//            return response()->json(['error' => 'Not authenticated'], 401);
//        }

        $filters = $request->only(['unread']); // Add more filters if needed
        $page = (int) $request->query('page', 1); // Default to page 1 if not provided.
        $perPage = (int) $request->query('per_page', 15);

        $notifications = $this->notificationService->getNotifiableNotifications(
            $notifiable->id,
            get_class($notifiable),
            $filters,
            $perPage,
            $page
        );

        // Meta information includes the unread count.
        $meta = [
            'unread_count' => $this->notificationService->getUnreadCount($notifiable->id, get_class($notifiable)),
//            'current_page' => $notifications->currentPage(),
//            'last_page'    => $notifications->lastPage(),
//            'per_page'     => $notifications->perPage(),
//            'total'        => $notifications->total(),
        ];

        return $this->respondWithResourceCollection(NotificationResource::collection($notifications)->additional(['meta' => $meta]), '');
//        return (NotificationResource::collection($notifications))
//            ->additional(['meta' => $meta]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        $notifiable = auth()->user(); // Adjust as needed.

        if (!$notifiable) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        return $this->apiResponse(
            [
                'success' => true,
                'result' => [
                    'count' => $this->notificationService->getUnreadCount($notifiable->id, get_class($notifiable))
                ],
                'message' => ''
            ], 200
        );

return $this->respondSuccess('success', $this->notificationService->getUnreadCount($notifiable->id, get_class($notifiable)));
        return response()->json([
            'count' => $this->notificationService->getUnreadCount($notifiable->id, get_class($notifiable))
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $notifiable = auth()->user(); // Adjust as needed.

        $this->notificationService->markAsRead($id, $notifiable->id, get_class($notifiable));
        return $this->respondSuccess('Notification marked as read');
//        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $notifiable = auth()->user(); // Adjust as needed.

        if (!$notifiable) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $updatedCount = $this->notificationService->markAllAsRead($notifiable->id, get_class($notifiable));
        return $this->respondSuccess("Marked $updatedCount notifications as read.");
//        return response()->json([
//            'message' => "Marked $updatedCount notifications as read."
//        ]);
    }
}

