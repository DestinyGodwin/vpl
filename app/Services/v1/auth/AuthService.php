<?php

namespace App\Services\v1\auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\v1\auth\RateLimitService;
use Illuminate\Support\Facades\RateLimiter;
use App\Notifications\v1\auth\EmailOtpNotification;
use App\Notifications\v1\auth\PasswordResetOtpNotification;

class AuthService
{
    /**
     * Create a new class instance.
     */

 public function __construct(private RateLimitService $rateLimiter) {}

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
    $maxAttempts = 3;
    $decaySeconds = 600;

    $check = $this->rateLimiter->check($key, $maxAttempts);
    if ($check) {
        return response()->json([
            'message' => $check['message'],
            'retry_after_seconds' => RateLimiter::availableIn($key),
            'remaining_attempts' => 0,
        ], $check['status']);
    }

    $this->rateLimiter->increment($key, $decaySeconds);

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

    return response()->json(['message' => 'OTP sent successfully.']);
}



public function verifyOtp(User $user, string $otp)
{
    $key = 'otp_verify:' . $user->id;
    $maxAttempts = 5;
    $decaySeconds = 600;

    $check = $this->rateLimiter->check($key, $maxAttempts);
    if ($check) {
        return response()->json([
            'message' => $check['message'],
            'retry_after_seconds' => RateLimiter::availableIn($key),
            'remaining_attempts' => 0,
        ], $check['status']);
    }

    if (
        $user->otp_code !== $otp ||
        $user->otp_expires_at < now()
    ) {
        $this->rateLimiter->increment($key, $decaySeconds);
        $attempts = RateLimiter::attempts($key);
        $remainingAttempts = max($maxAttempts - $attempts, 0);

        return response()->json([
            'message' => 'Invalid or expired OTP.',
            'remaining_attempts' => $remainingAttempts,
            'retry_after_seconds' => RateLimiter::availableIn($key),
        ], 401);
    }

    $this->rateLimiter->reset($key);

    $user->update([
        'email_verified_at' => now(),
        'otp_code' => null,
        'otp_expires_at' => null,
    ]);

    return response()->json(['message' => 'OTP verified successfully.']);
}
public function login(array $credentials)
{
    $email = $credentials['email'] ?? request()->ip();

    $attemptKey = "login:attempts:{$email}";
    $lockoutKey = "login:lockout:{$email}";
    $escalationKey = "login:escalation:{$email}";
    $maxAttempts = 5;

    // Step 1: If locked out, apply wait
    if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
        $retryAfter = RateLimiter::availableIn($lockoutKey);

        return [
            'success' => false,
            'message' => 'Too many login attempts. Try again later.',
            'retry_after_seconds' => $retryAfter,
            'remaining_attempts' => 0,
            'status' => 429
        ];
    }

    // Step 2: If failed too many times, escalate and lock
    $attempts = RateLimiter::attempts($attemptKey);
    if ($attempts >= $maxAttempts) {
        $failCount = RateLimiter::attempts($escalationKey);
        $cooldown = 60 * pow(2, $failCount); // 60, 120, 240, ...

        // Set lockout with escalating cooldown
        RateLimiter::hit($lockoutKey, $cooldown);

        // Increase escalation count
        RateLimiter::hit($escalationKey, 3600); // Keep escalation count for 1 hour

        return [
            'success' => false,
            'message' => "Account locked due to too many attempts. Try again in {$cooldown} seconds.",
            'retry_after_seconds' => $cooldown,
            'remaining_attempts' => 0,
            'status' => 429
        ];
    }

    // Step 3: If credentials are wrong
    if (!Auth::attempt($credentials)) {
        RateLimiter::hit($attemptKey, 600); // Each attempt lives for 10 mins
        $remaining = max($maxAttempts - ($attempts + 1), 0);

        return [
            'success' => false,
            'message' => 'Invalid credentials.',
            'remaining_attempts' => $remaining,
            'retry_after_seconds' => null,
            'status' => 401
        ];
    }

    RateLimiter::clear($attemptKey);
    RateLimiter::clear($lockoutKey);
    RateLimiter::clear($escalationKey);

    return [
        'success' => true,
        'token' => Auth::user()->createToken('auth_token')->plainTextToken
    ];
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