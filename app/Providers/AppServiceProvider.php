<?php

namespace App\Providers;

use App\Enums\PageEnum;
use App\Models\Page;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            Blade::directive('price', function ($price) {
                return "<?php echo number_format($price); ?>";
            });

            $locales = Config::get('translatable.locales');
            $view->with('locales', $locales);

            $view->with('brand', 'Luzori');

            $loggin = false;
            if (str_contains(url()->current(), 'admin')) {
                if (auth('admin')->check()) {
                    $brand = Page::where('type', PageEnum::WebsiteName->value)->first()?->value;
                    $view->with('brand', $brand);
                    $user = auth('admin')->user();
                    $loggin = true;
                }
            } else if (str_contains(url()->current(), 'center_user')) {
                // Skip auth check on login page to avoid DB connection issues before tenant is set
                if (!str_contains(url()->current(), 'center_user/login')) {
                    try {
                        if (auth('center_user')->check()) {
                            $brand = Page::where('type', PageEnum::WebsiteName->value)->first()?->value;
                            $view->with('brand', $brand);
                            $user = auth('center_user')->user();
                            $loggin = true;
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error in AppServiceProvider for center_user auth check: " . $e->getMessage());
                    }
                }
            }

            if ($loggin) {
                // $number_notifications = $user->notifications()->where('is_read', 0)->count();
                // $notis = $user->notifications()->latest()->get();

                $number_notifications = 10;
                $notis = [];

                $view->with('number_notifications', $number_notifications);
                $view->with('notis', $notis);
            }
        });

        // JsonResource::withoutWrapping();

        Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
            if ($src !== null) {
                return [
                    'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' : (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
                ];
            }
            return [];
        });
    }
}
