<?php

namespace App\Http\Middleware;

use App\Models\Center;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetActiveCenterCp
{
    /** Routes allowed while the center subscription is expired. */
    private const EXPIRED_ALLOWED_ROUTES = [
        'center_user.subscription.plans',
        'center_user.subscription.create-session',
        'center_user.subscription.callback',
        'center_user.logout',
        'center_user.swap',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = $this->resolveSubdomain($host, $parts);

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            $center = Center::where('domain', 'center5')->first();

            return $this->applyCenter($request, $next, $center);
        }   

        if ($subdomain && $subdomain !== 'dashboard') {
            $center = Center::where('domain', $subdomain)->first();

            if (!$center) {
                abort(404, 'Center not found');
            }

            return $this->applyCenter($request, $next, $center);
        }

        if (($subdomain === 'dashboard' || !$subdomain) && session()->has('active_center_domain')) {
            $center = Center::where('domain', session('active_center_domain'))->first();

            return $this->applyCenter($request, $next, $center);
        }

        return $next($request);
    }

    private function applyCenter(Request $request, Closure $next, ?Center $center): Response
    {
        if (!$center) {
            return $next($request);
        }

        $this->configureCenterDatabase($center);

        if ($this->centerIsExpired($center) && !$this->isSubscriptionRenewalRoute($request)) {
            return $this->redirectToRenewal($request);
        }

        return $next($request);
    }

    private function configureCenterDatabase(Center $center): void
    {
        Config::set('database.connections.mysql.database', $center->database);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    private function centerIsExpired(Center $center): bool
    {
        return $center->expire_date && now()->gt($center->expire_date);
    }

    private function isSubscriptionRenewalRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        return $routeName && in_array($routeName, self::EXPIRED_ALLOWED_ROUTES, true);
    }

    private function redirectToRenewal(Request $request): Response
    {
        $message = __('api.center_expired');
        $plansUrl = route('center_user.subscription.plans');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => false,
                'expired'  => true,
                'redirect' => $plansUrl,
                'message'  => $message,
            ], 402);
        }

        return redirect()
            ->to($plansUrl)
            ->with('center_expired', true)
            ->with('warning', $message);
    }

    private function resolveSubdomain(string $host, array $parts): ?string
    {
        if (count($parts) > 2 && $parts[0] !== 'www') {
            return $parts[0];
        }

        if (count($parts) > 3 && $parts[0] === 'www') {
            return $parts[1];
        }

        return null;
    }
}
