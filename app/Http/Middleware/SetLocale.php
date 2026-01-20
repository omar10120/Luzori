<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
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
        // Check if locale is set in session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            
            // Validate locale
            if (in_array($locale, ['en', 'ar'])) {
                App::setLocale($locale);
            }
        } else {
            // Set default locale based on browser or default to English
            $locale = $request->getPreferredLanguage(['en', 'ar']) ?? 'en';
            App::setLocale($locale);
            Session::put('locale', $locale);
        }

        return $next($request);
    }
}
