<?php

namespace App\Services\v1\auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\Notification\v1\auth\EmailOtpNotification;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function register(array $data): User
    {
        if (isset($data['profile_picture'])) {
            $path = $data['profile_picture']->store('profile_pictures', 'public');
            $data['profile_picture'] = $path;
        }
       
        $user = User::create($data);

        try {
            $user->sendEmailVerificationOtp(); 
        } catch (\Throwable $e) {
            Log::error('OTP email failed: ' . $e->getMessage());
        }

        return $user;
    }

    public function sendOtp(User $user)
    {
        $otp = rand(100000, 999999);
        $user->update([
            'email_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        try {
            $user->notify(new EmailOtpNotification($otp));
            Log::info("OTP sent to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to send OTP: " . $e->getMessage());
        }
    }

    public function verifyOtp(User $user, string $otp)
    {
        if (
            $user->email_otp !== $otp ||
            $user->otp_expires_at < now()
        ) {
            return false;
        }

        $user->update([
            'email_verified_at' => now(),
            'email_otp' => null,
            'otp_expires_at' => null,
        ]);

        return true;
    }

    public function login(array $credentials)
    {
        if (!Auth::attempt($credentials)) {
            return null;
        }

        return Auth::user()->createToken('auth_token')->plainTextToken;
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }
}



