<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('center_api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        $this->routes(function () {
            Route::middleware('center_api')
                ->prefix('center_api')
                ->namespace($this->namespace)
                ->group(base_path('routes/center_api.php'));
            Route::middleware('admin')
                ->prefix('admin')
                ->name('admin.')
                ->namespace($this->namespace)
                ->group(base_path('routes/admin.php'));
            Route::middleware('center_user')
                ->prefix('center_user')
                ->name('center_user.')
                ->namespace($this->namespace)
                ->group(base_path('routes/center_user.php'));
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }
}
