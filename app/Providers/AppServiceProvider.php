<?php

namespace App\Providers;

use App\Enums\PageEnum;
use App\Models\Center;
use App\Models\Page;
use App\Services\AppNotificationService;
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
        Blade::directive('price', function ($price) {
            return "<?php echo number_format($price); ?>";
        });

        view()->composer('*', function ($view) {
            $locales = Config::get('translatable.locales');
            $view->with('locales', $locales);
            $view->with('brand', 'Luzori');
            $view->with('number_notifications', 0);
            $view->with('notis', collect());
            $view->with('notifications_view_all_url', 'javascript:void(0)');
            $view->with('notifications_mark_all_url', null);
            $view->with('notifications_mark_one_url', null);
            $view->with('notifications_context', null);

            $loggin = false;
            $context = null;

            if (str_contains(url()->current(), 'admin')) {
                if (auth('admin')->check()) {
                    $brand = Page::where('type', PageEnum::WebsiteName->value)->first()?->value;
                    $view->with('brand', $brand);
                    $loggin = true;
                    $context = 'admin';
                }
            } elseif (str_contains(url()->current(), 'center_user')) {
                if (!str_contains(url()->current(), 'center_user/login')) {
                    try {
                        if (auth('center_user')->check()) {
                            $brand = Page::where('type', PageEnum::WebsiteName->value)->first()?->value;
                            $view->with('brand', $brand);
                            $loggin = true;
                            $context = 'center_user';
                        }
                    } catch (\Exception $e) {
                        \Log::error('Error in AppServiceProvider for center_user auth check: ' . $e->getMessage());
                    }
                }
            }

            if ($loggin && $context) {
                try {
                    $service = app(AppNotificationService::class);

                    if ($context === 'center_user') {
                        $currentDb = Config::get('database.connections.mysql.database');
                        $center = $currentDb
                            ? Center::where('database', $currentDb)->first()
                            : null;
                        if (!$center && session()->has('active_center_domain')) {
                            $center = Center::where('domain', session('active_center_domain'))->first();
                        }

                        if ($center) {
                            $nav = $service->navbarForCenter($center);
                            $view->with('number_notifications', $nav['unread']);
                            $view->with('notis', $nav['items']);
                            $view->with('notifications_view_all_url', route('center_user.notifications.inbox'));
                            $view->with('notifications_mark_all_url', route('center_user.notifications.markSeen'));
                            $view->with('notifications_mark_one_url', route('center_user.notifications.markSeen'));
                        }
                    } else {
                        $nav = $service->navbarForAdmin();
                        $view->with('number_notifications', $nav['unread']);
                        $view->with('notis', $nav['items']);
                        $view->with('notifications_view_all_url', route('admin.notifications.index'));
                    }

                    $view->with('notifications_context', $context);
                } catch (\Throwable $e) {
                    \Log::warning('Navbar notifications load failed: ' . $e->getMessage());
                }
            }
        });

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
