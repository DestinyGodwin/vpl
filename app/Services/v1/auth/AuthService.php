<?php

namespace App\Services\v1\auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Services\v1\auth\RateLimitService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
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
 
    
    /**
     * Initial waiting time in minutes after exceeding max attempts
     */const MAX_ATTEMPTS = 5;
    
    /**
     * Initial timeout in minutes
     */
    const INITIAL_TIMEOUT = 1;
    
    /**
     * Cache key prefix for attempts
     */
    const ATTEMPTS_KEY_PREFIX = 'login_attempts:';
    
    /**
     * Cache key prefix for lockout time
     */
    const LOCKOUT_KEY_PREFIX = 'login_lockout:';
    
    /**
     * Handle user login with exponential backoff rate limiting
     *
     * @param array $credentials
     * @param string $ipAddress
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials, string $ipAddress): array
    {
        $email = $credentials['email'];
        $key = $this->getKey($email, $ipAddress);
        
        $this->checkLockout($key);
        if (!Auth::attempt($credentials)) {
                        $this->handleFailedAttempt($key);
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
        $this->clearRateLimiting($key);
        $user = Auth::user();
        
        // Revoke any existing tokens for this user
        $user->tokens()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        return [
            'user' => $user->email,
            'university' => $user->university->name,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
    
    /**
     * Check if the user is currently locked out
     *
     * @param string $key
     * @throws ValidationException
     */
    private function checkLockout(string $key): void
    {
        $lockoutExpiration = Cache::get(self::LOCKOUT_KEY_PREFIX . $key);
        
        if ($lockoutExpiration && now()->lt($lockoutExpiration)) {
            $seconds = Carbon::now()->diffInSeconds($lockoutExpiration);
            
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }
    }
    
    /**
     * Handle a failed login attempt with exponential backoff
     *
     * @param string $key
     * @return void
     */
    private function handleFailedAttempt(string $key): void
    {
        $attemptsKey = self::ATTEMPTS_KEY_PREFIX . $key;
        $lockoutKey = self::LOCKOUT_KEY_PREFIX . $key;
        $attempts = Cache::get($attemptsKey, 0);
        $attempts++;
        Cache::put($attemptsKey, $attempts, now()->addDay());        
        if ($attempts > self::MAX_ATTEMPTS) {
            $exceededCount = $attempts - self::MAX_ATTEMPTS;
            $timeoutMinutes = self::INITIAL_TIMEOUT * pow(2, $exceededCount - 1);
            $lockoutExpiration = now()->addMinutes($timeoutMinutes);
            Cache::put($lockoutKey, $lockoutExpiration, $lockoutExpiration);
        }
    }
    
    /**
     * Clear all rate limiting data on successful login
     *
     * @param string $key
     * @return void
     */
    private function clearRateLimiting(string $key): void
    {
        Cache::forget(self::ATTEMPTS_KEY_PREFIX . $key);
        Cache::forget(self::LOCKOUT_KEY_PREFIX . $key);
    }
    
    /**
     * Get the unique key for rate limiting
     *
     * @param string $email
     * @param string $ipAddress
     * @return string
     */
    private function getKey(string $email, string $ipAddress): string
    {
        return Str::transliterate(Str::lower($email) . '|' . $ipAddress);
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

    // Check if university_id is changing
    $newUniversityId = $request->input('university_id');
    $oldUniversityId = $user->university_id;

    $user->fill($request->only([
        'first_name', 'last_name', 'phone', 'email', 'university_id'
    ]));

    $user->save();

    // Update user's stores if university has changed
    if ($newUniversityId && $newUniversityId !== $oldUniversityId) {
        $user->stores()->update(['university_id' => $newUniversityId]);
    }

    return $user->fresh()->load('university');
}
//hello test
    public function getProfile()
{
    return Auth::user()->load('university');
}
public function changePassword($user, $currentPassword, $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'Current password is incorrect.',
            ];
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return [
            'success' => true,
            'message' => 'Password changed successfully.',
        ];
    }

}