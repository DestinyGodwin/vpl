<?php

namespace App\Services\v1\auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\v1\GenericNotification;

class AdminService
{
    public function getAllUsers($perPage = 50)
    {
        return User::with('university')->paginate($perPage);
    }
    public function getUserById(string $id): User
    {
        return User::findOrFail($id);
    }

    public function getUserByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function getUsersByUniversity($universityId, $perPage = 50)
    {
        return User::where('university_id', $universityId)->with('university')->paginate($perPage);
    }

    public function getUsersByState($state, $perPage = 50)
    {
        return User::whereHas(
            'university',
            fn($q) =>
            $q->whereRaw('LOWER(state) = ?', [strtolower($state)])
        )->with('university')->paginate($perPage);
    }

    public function getUsersByCountry($country, $perPage = 50)
    {
        return User::whereHas(
            'university',
            fn($q) =>
            $q->whereRaw('LOWER(country) = ?', [strtolower($country)])
        )->with('university')->paginate($perPage);
    }

    public function notifyUsers($users, $title, $message): void
    {
        try {
            Notification::send($users, new GenericNotification($title, $message));

            Log::info('Notification sent successfully.', [
                'title' => $title,
                'message' => $message,
                'user_count' => count($users),
                'user_ids' => collect($users)->pluck('id')->toArray()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send notification.', [
                'error' => $e->getMessage(),
                'title' => $title,
                'message' => $message,
                'user_ids' => collect($users)->pluck('id')->toArray()
            ]);
        }
    }

    public function notifyUsersByUniversity(string $universityId, string $title, string $message): void
    {
        $users = User::where('university_id', $universityId)->get();
        Log::info("Fetched users by university", ['university_id' => $universityId, 'count' => $users->count()]);
        $this->notifyUsers($users, $title, $message);
    }

    public function notifyUsersByState(string $state, string $title, string $message): void
    {
        $users = User::whereHas(
            'university',
            fn($query) =>
            $query->whereRaw('LOWER(state) = ?', [strtolower($state)])
        )->get();
        Log::info("Fetched users by state", ['state' => $state, 'count' => $users->count()]);
        $this->notifyUsers($users, $title, $message);
    }

    public function notifyUsersByCountry(string $country, string $title, string $message): void
    {
        $users = User::whereHas(
            'university',
            fn($query) =>
            $query->whereRaw('LOWER(country) = ?', [strtolower($country)])
        )->get();
        Log::info("Fetched users by country", ['country' => $country, 'count' => $users->count()]);
        $this->notifyUsers($users, $title, $message);
    }

    public function notifyUsersByEmail($users, string $title, string $message): void
    {
        try {
            Notification::send($users, new GenericNotification($title, $message));

            Log::info('Notification sent successfully by email list.', [
                'title' => $title,
                'message' => $message,
                'user_ids' => collect($users)->pluck('id')->toArray()
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send notification by email list.', [
                'error' => $e->getMessage(),
                'title' => $title,
                'message' => $message,
                'user_ids' => collect($users)->pluck('id')->toArray()
            ]);
        }
    }
}
