<?php

namespace App\Services\v1\auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\v1\auth\RateLimitService;
use App\Notifications\v1\auth\EmailOtpNotification;
use App\Notifications\v1\auth\PasswordResetOtpNotification;

class AuthService
{
    /**
     * Create a new class instance.
     */

     protected RateLimitService $rateLimiter;

public function __construct(RateLimitService $rateLimiter)
{
    $this->rateLimiter = $rateLimiter;
}
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
    $key = "otp:{$type}:" . $user->id;

    if ($this->rateLimiter->tooManyAttempts($key, 3, 10)) {
        abort(429, 'Too many OTP requests. Try again in ' . $this->rateLimiter->availableIn($key) . ' seconds.');
    }

    $this->rateLimiter->hit($key, 10);

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
    $key = 'otp_verify:' . $user->id;

    if ($this->rateLimiter->tooManyAttempts($key, 5, 10)) {
        abort(429, 'Too many incorrect OTP attempts. Try again in ' . $this->rateLimiter->availableIn($key) . ' seconds.');
    }

    if (
        $user->otp_code !== $otp ||
        $user->otp_expires_at < now()
    ) {
        $this->rateLimiter->hit($key, 10);
        return false;
    }

    $this->rateLimiter->clear($key);

    $user->update([
        'email_verified_at' => now(),
        'otp_code' => null,
        'otp_expires_at' => null,
    ]);

    return true;
}

public function login(array $credentials)
{
    $key = 'login:' . request()->ip();

    if ($this->rateLimiter->tooManyAttempts($key, 5, 1)) {
        abort(429, 'Too many login attempts. Try again in ' . $this->rateLimiter->availableIn($key) . ' seconds.');
    }

    if (!Auth::attempt($credentials)) {
        $this->rateLimiter->hit($key, 1);
        return null;
    }

    $this->rateLimiter->clear($key);
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