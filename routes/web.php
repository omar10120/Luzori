<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::group(['domain' => '{subdomain}.technosystems.online'], function () {
//     Route::get('/', function ($subdomain) {
//         return "Welcome to the $subdomain subdomain!";
//     });
//     Route::get('branches', function () {
//         return Branch::all();
//     });
// });

Route::get('/', function () {
    return view('welcome');
});

// Contact form route
Route::post('/contact/send', [App\Http\Controllers\ContactController::class, 'sendMessage'])->name('contact.send');

// Language switcher route
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('optimize', function () {
    Artisan::call('optimize');
    return "Optimization completed successfully!";
});

Route::get('optimize-clear', function () {
    Artisan::call('optimize:clear');
    return "Optimization completed successfully!";
});

Route::get('clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('optimize:clear');
    Artisan::call('route:clear');
    return "Cache cleared successfully";
});

