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
        // HTTPS ends at Nginx Proxy Manager on the LAN, which forwards to this PC over HTTP.
        // Trust only that host, so visitors cannot spoof X-Forwarded-* headers.
        $middleware->trustProxies(at: ['192.168.0.33']);

        $middleware->web(append: [
            \App\Http\Middleware\EnsurePasswordIsChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
