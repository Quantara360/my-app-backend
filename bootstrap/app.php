<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // This is an API-only app - there is no 'login' web route to
        // redirect guests to. Without this, Laravel's default Authenticate
        // middleware calls route('login') for any unauthenticated request
        // that doesn't send an exact `Accept: application/json` header,
        // which throws RouteNotFoundException (an unhandled 500) before an
        // AuthenticationException is even constructed - so the render()
        // handler below never gets a chance to run either.
        $middleware->redirectGuestsTo(fn () => null);

        // Register security middleware
        $middleware->api([
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\SanitizeInputMiddleware::class,
            \App\Http\Middleware\RateLimitMiddleware::class,
            \App\Http\Middleware\SecureHeadersMiddleware::class,
        ]);
        
        // Throttle sensitive endpoints more aggressively
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // This is an API-only app - there is no 'login' web route. Without
        // this, an unauthenticated request that doesn't send an exact
        // `Accept: application/json` header falls through to Laravel's
        // default Authenticate::redirectTo(), which tries to generate a
        // URL for the (nonexistent) 'login' route and throws
        // RouteNotFoundException - turning what should be a clean 401 into
        // an unhandled 500 for any client that isn't perfectly well-behaved.
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })->create();
