<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (str_contains(url()->current(), 'admin')) {
            $route = route('admin.login');
        } else if (str_contains(url()->current(), 'center_user')) {
            $route = route('center_user.login');
        } else {
            $route = null;
        }
        return $request->expectsJson() ? null : $route;
    }
}
