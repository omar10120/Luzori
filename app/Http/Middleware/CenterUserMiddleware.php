<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CenterUserMiddleware
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                auth()->shouldUse($guard);
                return $next($request);
            }
        }
        return abort(401);
    }
}
