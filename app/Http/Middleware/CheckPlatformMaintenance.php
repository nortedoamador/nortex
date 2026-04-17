<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\PlatformMaintenance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlatformMaintenance
{
    /**
     * @var list<string>
     */
    private const EXEMPT_ROUTE_NAMES = [
        'login',
        'password.request',
        'password.email',
        'password.reset',
        'password.store',
        'auth.lookup-email',
        'logout',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'password.confirm',
        'password.update',
        'stripe.webhook',
        'home',
        'platform.impersonate.stop',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! PlatformMaintenance::enabled()) {
            return $next($request);
        }

        if ($this->bypassUser($request)) {
            return $next($request);
        }

        $route = $request->route();
        if ($route !== null && in_array($route->getName(), self::EXEMPT_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if ($request->is('stripe/webhook')) {
            return $next($request);
        }

        return response()
            ->view('errors.platform-maintenance', [], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    private function bypassUser(Request $request): bool
    {
        $user = $request->user();
        if ($user?->is_platform_admin || $user?->is_master_admin) {
            return true;
        }

        $impersonatorId = (int) $request->session()->get('impersonator_id', 0);
        if ($impersonatorId <= 0) {
            return false;
        }

        $impersonator = User::query()->find($impersonatorId);

        return $impersonator !== null && ($impersonator->is_platform_admin || $impersonator->is_master_admin);
    }
}
