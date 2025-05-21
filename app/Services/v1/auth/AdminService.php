<?php

namespace App\Services\v1\auth;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\v1\GenericNotification;

class AdminService
{
    public function getAllUsers($perPage = 50)
    {
        return User::with('university')->paginate($perPage);
    }
     public function getUserById(int $id): User
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
        return User::whereHas('university', fn($q) =>
            $q->whereRaw('LOWER(state) = ?', [strtolower($state)])
        )->with('university')->paginate($perPage);
    }

    public function getUsersByCountry($country, $perPage = 50)
    {
        return User::whereHas('university', fn($q) =>
            $q->whereRaw('LOWER(country) = ?', [strtolower($country)])
        )->with('university')->paginate($perPage);
    }

    public function notifyUsers($users, $title, $message): void
    {
        Notification::send($users, new GenericNotification($title, $message));
    }

    public function notifyUsersByUniversity(string $universityId, string $title, string $message): void
    {
        $users = User::where('university_id', $universityId)->get();
        $this->notifyUsers($users, $title, $message);
    }

    public function notifyUsersByState(string $state, string $title, string $message): void
    {
        $users = User::whereHas('university', fn($query) =>
            $query->whereRaw('LOWER(state) = ?', [strtolower($state)])
        )->get();
        $this->notifyUsers($users, $title, $message);
    }

    public function notifyUsersByCountry(string $country, string $title, string $message): void
    {
        $users = User::whereHas('university', fn($query) =>
            $query->whereRaw('LOWER(country) = ?', [strtolower($country)])
        )->get();
        $this->notifyUsers($users, $title, $message);
    }
}
