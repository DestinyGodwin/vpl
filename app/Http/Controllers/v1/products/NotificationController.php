<?php

namespace App\Http\Controllers\v1\products;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\products\NotificationService;

class NotificationController extends Controller
{
   
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        return response()->json([
            'notifications' => $this->notificationService->getAll()
        ]);
    }
public function show(string $id)
{
    $notification = $this->notificationService->getOne($id);

    return response()->json(['notification' => $notification]);
}
    public function unread(Request $request)
    {
        return response()->json([
            'notifications' => $this->notificationService->getUnread()
        ]);
    }

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
}
