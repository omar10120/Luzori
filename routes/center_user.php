<?php

use App\Http\Controllers\CenterUser\BranchController;
use App\Http\Controllers\CenterUser\PaymentMethodController;
use App\Http\Controllers\CenterUser\WalletController;
use App\Http\Controllers\CenterUser\DeleteController;
use App\Http\Controllers\CenterUser\DiscountController;
use App\Http\Controllers\CenterUser\ExpensesController;
use App\Http\Controllers\CenterUser\SuppliersController;
use App\Http\Controllers\CenterUser\WorkerController;
use App\Http\Controllers\CenterUser\HomeController;
use App\Http\Controllers\CenterUser\LanguageController;
use App\Http\Controllers\CenterUser\LoginController;
use App\Http\Controllers\CenterUser\MembershipController;
use App\Http\Controllers\CenterUser\NotificationController;
use App\Http\Controllers\CenterUser\PackageController;
use App\Http\Controllers\CenterUser\BookingController;
use App\Http\Controllers\CenterUser\BookingWithTipsController;
use App\Http\Controllers\CenterUser\BuyProductController;
use App\Http\Controllers\CenterUser\SalesController;
use App\Http\Controllers\CenterUser\ProductController;
use App\Http\Controllers\CenterUser\ServiceController;
use App\Http\Controllers\CenterUser\StocktakeController;
use App\Http\Controllers\CenterUser\ShiftController;
use App\Http\Controllers\CenterUser\UserController;
use App\Http\Controllers\CenterUser\VacationController;
use App\Http\Controllers\CenterUser\UserWalletController;
use App\Http\Controllers\CenterUser\CenterUserController;
use App\Http\Controllers\CenterUser\CenterUserRoleController;
use App\Http\Controllers\CenterUser\Report\CommissionReportController;
use App\Http\Controllers\CenterUser\Report\DailyReportController;
use App\Http\Controllers\CenterUser\Report\ExpenseReportController;
use App\Http\Controllers\CenterUser\Report\SalesReportController;
use App\Http\Controllers\CenterUser\Report\StaffReportController;
use App\Http\Controllers\CenterUser\Report\TipsReportController;
use App\Http\Controllers\CenterUser\InfoController;
use App\Http\Controllers\CenterUser\PageController;
use App\Http\Controllers\CenterUser\SettingController;
use App\Http\Controllers\CenterUser\WeekDayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Center User Routes
|--------------------------------------------------------------------------
|
| Here is where you can register center routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "center" middleware group. Now create something great!
|
 */

// Broadcast::routes(); // for /broadcasting/auth

Route::get('login', [LoginController::class, 'index'])->name('login');

Route::post('login', [LoginController::class, 'authenticate'])->name('login');

// 2FA Verification routes
Route::get('verify', [LoginController::class, 'showVerifyForm'])->name('verify.show');
Route::post('verify', [LoginController::class, 'verifyCode'])->name('verify');
Route::post('verify/resend', [LoginController::class, 'resendCode'])->name('verify.resend');

Route::group(['middleware' => 'auth_center_user:center_user'], function () {
    Route::get('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('cp', [HomeController::class, 'cp'])->name('cp');
    Route::get('dashboard/details/{type}', [HomeController::class, 'getDetails'])->name('dashboard.details');

    Route::get('swap', [LanguageController::class, 'swap'])->name('swap');

    Route::delete('delete', DeleteController::class)->name('delete');

    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('show', 'show')->name('show');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'centeruserroles', 'as' => 'centeruserroles.'], function () {
        Route::controller(CenterUserRoleController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'centerusers', 'as' => 'centerusers.'], function () {
        Route::controller(CenterUserController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::post('changeStatusWeb', 'changeStatusWeb')->name('changeStatusWeb');
        });
    });

    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::controller(NotificationController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('show', 'show')->name('show');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'branches', 'as' => 'branches.'], function () {
        Route::controller(BranchController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'payment_methods', 'as' => 'payment_methods.'], function () {
        Route::controller(PaymentMethodController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'weeksdays', 'as' => 'weeksdays.'], function () {
        Route::controller(WeekDayController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::post('change-status', 'changeStatus')->name('changeStatus');
        });
    });

    Route::group(['prefix' => 'shifts', 'as' => 'shifts.'], function () {
        Route::controller(ShiftController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
        Route::controller(ServiceController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('info', 'info')->name('info');
        });
    });

    Route::group(['prefix' => 'workers', 'as' => 'workers.'], function () {
        Route::controller(WorkerController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('get-workers-by-service', 'getWorkersByService')->name('get-workers-by-service');
            Route::get('get-workers-by-branch', 'getWorkersByBranch')->name('get-workers-by-branch');
            Route::get('info', 'info')->name('info');
        });
    });

    Route::group(['prefix' => 'vacations', 'as' => 'vacations.'], function () {
        Route::controller(VacationController::class)->group(function () {
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'memberships', 'as' => 'memberships.'], function () {
        Route::controller(MembershipController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'discounts', 'as' => 'discounts.'], function () {
        Route::controller(DiscountController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'wallets', 'as' => 'wallets.'], function () {
        Route::controller(WalletController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'users_wallets', 'as' => 'users_wallets.'], function () {
        Route::controller(UserWalletController::class)->group(function () {
            Route::get('showUsers', 'showUsers')->name('showUsers');
            Route::get('print', 'print')->name('print');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'packages', 'as' => 'packages.'], function () {
        Route::controller(PackageController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'products', 'as' => 'products.'], function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::post('add-brand', 'addBrand')->name('add-brand');
            Route::post('add-category', 'addCategory')->name('add-category');
            Route::post('add-supplier', 'addSupplier')->name('add-supplier');
        });
    });

    Route::group(['prefix' => 'buyproducts', 'as' => 'buyproducts.'], function () {
        Route::controller(BuyProductController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('print', 'print')->name('print');
        });
    });

    Route::group(['prefix' => 'stocktakes', 'as' => 'stocktakes.'], function () {
        Route::controller(StocktakeController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('start/{id}', 'start')->name('start');
            Route::get('count/{id}', 'count')->name('count');
            Route::post('update-count/{id}', 'updateCount')->name('update-count');
            Route::get('complete/{id}', 'complete')->name('complete');
            Route::get('details/{id}', 'details')->name('details');
            Route::post('review/{id}', 'review')->name('review');
        });
    });

    Route::group(['prefix' => 'expenses', 'as' => 'expenses.'], function () {
        Route::controller(ExpensesController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('providers', 'providers')->name('providers');
            Route::post('update-provider', 'updateProvider')->name('update.provider');
        });
    });

    Route::group(['prefix' => 'suppliers', 'as' => 'suppliers.'], function () {
        Route::controller(SuppliersController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::post('update-supplier', 'updateSupplier')->name('update.supplier');
        });
    });

    Route::group(['prefix' => 'bookings', 'as' => 'bookings.'], function () {
        Route::controller(BookingController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('get-services-by-user', 'getServicesByUser')->name('get-services-by-user');
            Route::get('print', 'print')->name('print');
        });
    });

    Route::group(['prefix' => 'booking_with_tips', 'as' => 'booking_with_tips.'], function () {
        Route::controller(BookingWithTipsController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::get('get-workers', 'getWorkers')->name('get-workers');
            Route::get('print', 'print')->name('print');
        });
    });

    Route::group(['prefix' => 'sales', 'as' => 'sales.'], function () {
        Route::controller(SalesController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::match(['get', 'post'], 'cart', 'cart')->name('cart');
            Route::post('add-service-to-cart', 'addServiceToCart')->name('add-service-to-cart');
            Route::post('add-product-to-cart', 'addProductToCart')->name('add-product-to-cart');
            Route::post('remove-from-cart', 'removeFromCart')->name('remove-from-cart');
            Route::get('payment', 'payment')->name('payment');
            Route::post('process-payment', 'processPayment')->name('process-payment');
            Route::get('show/{id}', 'show')->name('show');
            Route::get('print/{id}', 'print')->name('print');
        });
    });

    Route::group(['prefix' => 'pages', 'as' => 'pages.'], function () {
        Route::controller(PageController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'infos', 'as' => 'infos.'], function () {
        Route::controller(InfoController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
            Route::post('update-sender-email', 'updateSenderEmail')->name('updateSenderEmail');
        });
    });

    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::controller(SettingController::class)->group(function () {
            Route::get('index', 'index')->name('index');
            Route::post('updateOrCreate', 'updateOrCreate')->name('updateOrCreate');
        });
    });

    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::controller(DailyReportController::class)->group(function () {
            Route::get('daily-report', 'daily_report')->name('daily-report');
        });
        Route::controller(SalesReportController::class)->group(function () {
            Route::get('sales-report', 'sales')->name('sales-report');
        });
        Route::controller(StaffReportController::class)->group(function () {
            Route::get('staff-report', 'staff')->name('staff-report');
        });
        Route::controller(CommissionReportController::class)->group(function () {
            Route::get('commission-report', 'commissions')->name('commission-report');
        });
        Route::controller(TipsReportController::class)->group(function () {
            Route::get('tips-report', 'tips')->name('tips-report');
            Route::get('get-users-by-branch', 'getUsersByBranch')->name('get-users-by-branch');
        });
        Route::controller(ExpenseReportController::class)->group(function () {
            Route::get('expense-report', 'expense_report')->name('expense-report');
        });
    });
});
