<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class Localization
{
    private Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale');
        $availLocale = config("translatable.locales");
        if (!in_array($locale, $availLocale) && !is_null($locale)) {
            abort(400);
        }

        if (is_null($locale)) {
            $locale = config("translatable.locale");
            session(["locale" => $locale]);
        }

        $this->application->setLocale($locale);
        config(['translatable.locale' => app()->getLocale()]);

        if ($locale == 'ar') {
            session()->put('direction', 'rtl');
        } else {
            session()->put('direction', 'ltr');
        }
        $acceptLanguage = $request->header('accept-language');
        if ($acceptLanguage && in_array($acceptLanguage, $availLocale)) {
            $this->application->setLocale($acceptLanguage);
            config(['translatable.locale' => app()->getLocale()]);
        }
        config(['custom.custom.myRTLMode' => $locale == "ar"]);
        return $next($request);
    }
}
