<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function __construct(protected RateLimiter $limiter)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        $limit = intval(env('API_RATE_LIMIT', 60));
        $decay = intval(env('API_RATE_LIMIT_PERIOD', 1));

        if ($this->limiter->tooManyAttempts($key, $limit, $decay)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.'
            ], 429);
        }

        $this->limiter->hit($key, $decay * 60);

        return $next($request)
            ->header('X-RateLimit-Limit', (string) $limit)
            ->header('X-RateLimit-Remaining', (string) max(0, $limit - $this->limiter->attempts($key)));
    }

    protected function resolveRequestSignature(Request $request): string
    {
        if ($request->user()?->id) {
            return 'user:' . $request->user()->id;
        }

        if ($token = $request->bearerToken()) {
            return 'token:' . hash('sha256', $token);
        }

        return 'ip:' . ($request->getClientIp() ?? 'unknown');
    }
}
