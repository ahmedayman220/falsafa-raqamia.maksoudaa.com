<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class WebhookRateLimit
{
    /**
     * Handle an incoming request.
     * 
     * Rate limiting middleware specifically designed for webhook endpoints
     * to handle high-pressure scenarios with 20M+ records.
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 1000, int $decayMinutes = 1): Response
    {
        // CRITICAL: Rate limiting for high-pressure webhook scenarios
        $key = 'webhook:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests',
                'retry_after' => RateLimiter::availableIn($key),
                'limit' => $maxAttempts,
                'window' => $decayMinutes . ' minute(s)',
            ], 429);
        }
        
        // Increment the rate limit counter
        RateLimiter::hit($key, $decayMinutes * 60);
        
        return $next($request);
    }
}
