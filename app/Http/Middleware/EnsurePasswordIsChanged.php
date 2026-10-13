<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Users imported from the legacy site had plain-text passwords, so they must
 * pick a new one before using the rest of the site.
 */
class EnsurePasswordIsChanged
{
    /**
     * Routes the user can still reach while a password change is pending.
     *
     * @var list<string>
     */
    private const ALLOWED_ROUTES = [
        'settings.password',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->password_must_change
            || $request->routeIs(...self::ALLOWED_ROUTES)
            || $request->is('livewire*')) {
            return $next($request);
        }

        return redirect()->route('settings.password');
    }
}
