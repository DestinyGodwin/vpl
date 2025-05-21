<?php

namespace App\Services\v1\auth;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\v1\GenericNotification;

class AdminService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    public function getAllUsers($perPage = 50)
    {
        return User::with('university')->paginate($perPage);
    }

    public function getUsersByUniversity($universityId, $perPage = 50)
    {
        return User::where('university_id', $universityId)
                   ->with('university')
                   ->paginate($perPage);
    }

    public function getUsersByState($state, $perPage = 50)
    {
        return User::whereHas('university', function ($q) use ($state) {
            $q->whereRaw('LOWER(state) = ?', [strtolower($state)]);
        })->with('university')->paginate($perPage);
    }

    public function getUsersByCountry($country, $perPage = 50)
    {
        return User::whereHas('university', function ($q) use ($country) {
            $q->whereRaw('LOWER(country) = ?', [strtolower($country)]);
        })->with('university')->paginate($perPage);
    }

    public function notifyUsers($users, $title, $message)
    {
        foreach ($users as $user) {
            $user->notify(new GenericNotification($title, $message));
        }
    }
    public function notifyUsersByUniversity(string $universityId, string $title, string $message): void
{
    $users = User::where('university_id', $universityId)->get();
    Notification::send($users, new GeneralNotification($title, $message));
}

public function notifyUsersByState(string $state, string $title, string $message): void
{
    $users = User::whereHas('university', function ($query) use ($state) {
        $query->whereRaw('LOWER(state) = ?', [strtolower($state)]);
    })->get();
    Notification::send($users, new GeneralNotification($title, $message));
}

public function notifyUsersByCountry(string $country, string $title, string $message): void
{
    $users = User::whereHas('university', function ($query) use ($country) {
        $query->whereRaw('LOWER(country) = ?', [strtolower($country)]);
    })->get();
    Notification::send($users, new GeneralNotification($title, $message));
}

}
