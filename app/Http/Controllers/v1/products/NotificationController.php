<?php

namespace App\Http\Controllers\v1\products;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\NotificationResource;
use App\Services\v1\products\NotificationService;

class NotificationController extends Controller
{
   
    public function __construct(protected NotificationService $notificationService) {}
    public function markAsRead(Request $request, string $id)
    {
        $this->notificationService->markAsRead($id);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(string $id)
    {
        $this->notificationService->delete($id);

        return response()->json(['message' => 'Notification deleted.']);
    }

    public function destroyAll()
    {
        $this->notificationService->deleteAll();

        return response()->json(['message' => 'All notifications deleted.']);
    }
    public function index(Request $request)
{
    $notifications = $this->notificationService->getAll();
    return response()->json([
        'notifications' => NotificationResource::collection($notifications)
    ]);
}

public function unread(Request $request)
{
    $notifications = $this->notificationService->getUnread();
    return response()->json([
        'notifications' => NotificationResource::collection($notifications)
    ]);
}

public function show(string $id)
{
    $notification = $this->notificationService->getOne($id);
    return response()->json([
        'notification' => new NotificationResource($notification)
    ]);
}
}
