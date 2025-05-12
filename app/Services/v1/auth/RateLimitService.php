<?php
namespace App\Services\v1\auth;
use Illuminate\Support\Facades\RateLimiter;


class RateLimitService
{
    public function tooManyAttempts(string $key, int $maxAttempts = 5, int $decayMinutes = 1): bool
    {
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    public function hit(string $key, int $decayMinutes = 1): void
    {
        RateLimiter::hit($key, $decayMinutes * 60);
    }

    public function clear(string $key): void
    {
        RateLimiter::clear($key);
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        return RateLimiter::remaining($key, $maxAttempts);
    }

    public function availableIn(string $key): int
    {
        return RateLimiter::availableIn($key);
    }
}
