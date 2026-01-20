<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (str_contains(url()->current(), 'admin')) {
            $verticalMenuJson = file_get_contents(base_path('resources/menu/adminMenu.json'));
        } else {
            $verticalMenuJson = file_get_contents(base_path('resources/menu/centerUserMenu.json'));
        }
        
        $verticalMenuData = json_decode($verticalMenuJson);
        view()->share('menuData', [$verticalMenuData]);
    }
}
