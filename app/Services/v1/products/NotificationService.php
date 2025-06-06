<?php

namespace App\Services\v1\products;

use Illuminate\Support\Facades\Auth;

class NotificationService
{public function getAll()
{
    return Auth::user()->notifications()->latest()->get();
}

public function getUnread()
{
    return Auth::user()->unreadNotifications()->latest()->get();
}

    public function markAsRead(string $notificationId): bool
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return true;
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function delete(string $notificationId): bool
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->delete();

        return true;
    }

    public function deleteAll(): void
    {
        Auth::user()->notifications()->delete();
    }
    public function getOne(string $notificationId)
{
    return Auth::user()->notifications()->findOrFail($notificationId);
}

}