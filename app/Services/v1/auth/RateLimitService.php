<?php
namespace App\Services\v1\auth;

use Illuminate\Support\Facades\RateLimiter;

class RateLimitService
{
    /**
     *
     * @param string $key
     * @param int $maxAttempts
     * @return array|null
     */
    public function check(string $key, int $maxAttempts): ?array
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return [
                'message' => "Too many attempts. Please try again in {$seconds} seconds.",
                'status' => 429,
            ];
        }
        return null;
    }

    /**
     * Increment the rate limiter for a given key.
     *
     * @param string $key
     * @param int $decaySeconds
     * @return void
     */
    public function increment(string $key, int $decaySeconds = 60): void
    {
        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Clear the rate limiter for a given key.
     *
     * @param string $key
     * @return void
     */
    public function reset(string $key): void
    {
        RateLimiter::clear($key);
    }
}
