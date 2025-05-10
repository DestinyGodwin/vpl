<?php

namespace App\Services\v1\auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Notifications\v1\auth\EmailOtpNotification;
use App\Notifications\v1\auth\PasswordResetOtpNotification;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    /**
     * Create a new class instance.
     */
    public function register(array $data) : string
    {
        if (isset($data['profile_picture'])) {
            $path = $data['profile_picture']->store('profile_pictures', 'public');
            $data['profile_picture'] = $path;
        }
       
        $user = User::create($data);

        try {
            $this->sendOtp($user);
           Log::info('OTP email sent successfully: ' );
        } catch (\Throwable $e) {
            Log::error('OTP email failed: ' . $e->getMessage());
        }

        return $user->email;
    }


    public function sendOtp(User $user, string $type = 'email_verification')
    {
        $otp = rand(100000, 999999);
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => now()->addMinutes(10)
        ]);
    
        try {
            if ($type === 'email_verification') {
                $user->notify(new EmailOtpNotification($otp));
            } elseif ($type === 'password_reset') {
                $user->notify(new PasswordResetOtpNotification($otp));
            }
    
            Log::info("OTP sent for {$type} to {$user->email}");
        } catch (\Throwable $e) {
            Log::error("Failed to send {$type} OTP: " . $e->getMessage());
        }
    }

    public function verifyOtp(User $user, string $otp)
    {
        if (
            $user->otp_code !== $otp ||
            $user->otp_expires_at < now()
        ) {
            return false;
        }

        // $user->update([
        //     'email_verified_at' => now(),
        //     'otp_code' => null,
        //     'otp_expires_at' => null,
        // ]);
        $user->email_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

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
 
    public function updateProfile($request)
    {
        $user = Auth::user();
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
        }
    
        $user->fill($request->only([
            'first_name', 'last_name', 'phone', 'email', 'university_id'
        ]));
    
        $user->save();
    
        return $user->fresh();
    }
    public function getProfile()
{
    return Auth::user()->load('university');
}


}