<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RateLimitService
{
    protected int $baseAttempts = 3;
    protected int $baseCooldownSeconds = 60;

    public function tooManyAttempts(string $key): bool
    {
        return Cache::has($this->getLockKey($key));
    }

    public function hit(string $key): array
    {
        $attemptKey = $this->getAttemptKey($key);
        $lockKey = $this->getLockKey($key);

        $attempts = Cache::increment($attemptKey, 1);
        $blockCount = (int) floor($attempts / $this->baseAttempts);

        if ($attempts % $this->baseAttempts === 0) {
            $cooldown = $this->baseCooldownSeconds * ($blockCount + 1); // Increase per block
            Cache::put($lockKey, now()->addSeconds($cooldown), $cooldown);
        }

        $remaining = max($this->baseAttempts - ($attempts % $this->baseAttempts), 0);
        $retryAfter = Cache::get($lockKey)?->diffInSeconds(now());

        return [
            'locked' => Cache::has($lockKey),
            'remaining_attempts' => $remaining,
            'retry_after_seconds' => $retryAfter,
        ];
    }

    public function clear(string $key): void
    {
        Cache::forget($this->getAttemptKey($key));
        Cache::forget($this->getLockKey($key));
    }

    private function getAttemptKey(string $key): string
    {
        return "ratelimit:attempts:$key";
    }

    private function getLockKey(string $key): string
    {
        return "ratelimit:lock:$key";
    }
}
