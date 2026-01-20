<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\CenterController;
use App\Http\Controllers\Admin\CenterRoleController;
use App\Http\Controllers\Admin\DeleteController;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\InfoController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
 */

// Broadcast::routes(); // for /broadcasting/auth

Route::get('login', function () {
    if (auth('admin')->user()) {
        return redirect()->route('admin.cp');
    }
    return view('Admin.login');
})->name('login');

Route::post('login', [LoginController::class, 'authenticate'])->name('login');

Route::group(['middleware' => 'auth:admin'], function () {
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('cp', [HomeController::class, 'cp'])->name('cp');

    Route::get('swap', [LanguageController::class, 'swap'])->name('swap');

    Route::delete('delete', DeleteController::class)->name('delete');

    Route::group(['prefix' => 'centerroles', 'as' => 'centerroles.'], function () {
        Route::controller(CenterRoleController::class)->group(function () {
            Route::get('index', 'index')->name('index')->can('VIEW_CENTERROLES');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'centers', 'as' => 'centers.'], function () {
        Route::controller(CenterController::class)->group(function () {
            Route::get('index', 'index')->name('index')->can('VIEW_CENTERS');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('permissions', 'permissions')->name('permissions');
            Route::post('update-permissions', 'updatePermissions')->name('update.permissions');
        });
    });

    

    Route::group(['prefix' => 'adminroles', 'as' => 'adminroles.'], function () {
        Route::controller(AdminRoleController::class)->group(function () {
            Route::get('index', 'index')->name('index')->can('VIEW_ADMINROLES');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'admins', 'as' => 'admins.'], function () {
        Route::controller(AdminController::class)->group(function () {
            Route::get('index', 'index')->name('index')->can('VIEW_ADMINS');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'pages', 'as' => 'pages.'], function () {
        Route::controller(PageController::class)->group(function () {
            Route::get('index', 'index')->name('index')->can('VIEW_PAGES');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate')->can('UPDATE_PAGES');
        });
    });

    Route::group(['prefix' => 'infos', 'as' => 'infos.'], function () {
        Route::controller(InfoController::class)->group(function () {
            Route::get('index', 'index')->name('index')->can('VIEW_INFOS');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate')->can('UPDATE_INFOS');
        });
    });

    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::controller(SettingController::class)->group(function () {
            Route::get('index', 'index')->name('index')->can('VIEW_SETTINGS');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate')->can('UPDATE_SETTINGS');
        });
    });
});
