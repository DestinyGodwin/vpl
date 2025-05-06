<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email is not verified. Please enter the OTP sent to your email.',
                'verify_otp_endpoint' => url('/api/verify-otp'),
                'resend_otp_endpoint' => url('/api/resend-otp'),
            ], 403);
        }

        return $next($request);
    }
}
