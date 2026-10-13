<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cloudflare -> Nginx Proxy Manager (LAN) -> this PC. Trust exactly those hops so the
        // visitor's real IP is used and nobody else can spoof X-Forwarded-* headers.
        $middleware->trustProxies(at: \App\Support\TrustedProxies::all());

        $middleware->web(append: [
            \App\Http\Middleware\EnsurePasswordIsChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
