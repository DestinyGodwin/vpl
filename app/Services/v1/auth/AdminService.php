<?php

namespace App\Services\v1\auth;

use App\Models\User;
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
}
