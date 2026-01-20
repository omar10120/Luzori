<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ClearCach
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        Artisan::call('cache:clear');
        // Artisan::call('config:clear');
        // Artisan::call('route:clear');
        // Artisan::call('optimize:clear');
        // Artisan::call('route:clear');
        return $next($request);
    }
}
