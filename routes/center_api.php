<?php

use App\Http\Controllers\CenterAPI\CenterUserController;
use App\Http\Controllers\CenterAPI\AuthController;
use App\Http\Controllers\CenterAPI\BranchController;
use App\Http\Controllers\CenterAPI\BookingController;
use App\Http\Controllers\CenterAPI\BookingWithTipsController;
use App\Http\Controllers\CenterAPI\BuyProductController;
use App\Http\Controllers\CenterAPI\DiscountController;
use App\Http\Controllers\CenterAPI\NotificationController;
use App\Http\Controllers\CenterAPI\ProfileController;
use App\Http\Controllers\CenterAPI\PageController;
use App\Http\Controllers\CenterAPI\SettingController;
use App\Http\Controllers\CenterAPI\InfoController;
use App\Http\Controllers\CenterAPI\MembershipController;
use App\Http\Controllers\CenterAPI\PackageController;
use App\Http\Controllers\CenterAPI\ProductController;
use App\Http\Controllers\CenterAPI\Report\CommissionReportController;
use App\Http\Controllers\CenterAPI\Report\DailyReportController;
use App\Http\Controllers\CenterAPI\Report\SalesReportController;
use App\Http\Controllers\CenterAPI\Report\StaffReportController;
use App\Http\Controllers\CenterAPI\Report\TipsReportController;
use App\Http\Controllers\CenterAPI\RoleController;
use App\Http\Controllers\CenterAPI\ServiceController;
use App\Http\Controllers\CenterAPI\ShiftController;
use App\Http\Controllers\CenterAPI\UserController;
use App\Http\Controllers\CenterAPI\UserWalletController;
use App\Http\Controllers\CenterAPI\WalletController;
use App\Http\Controllers\CenterAPI\WeekDayController;
use App\Http\Controllers\CenterAPI\WorkerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Api Center Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('forget', 'forget');
        Route::post('check-code', 'checkCode');
        Route::post('reset', 'reset');
    });
});

// Route::middleware('OptionalAuth:api')->group(function () {
// });

Route::group(['prefix' => 'pages'], function () {
    Route::controller(PageController::class)->group(function () {
		Route::get('all', 'all');
        Route::get('privacy-policy', 'privacyPolicy');
        Route::get('terms-conditions', 'termsConditions');
        Route::get('about-us', 'aboutUs');
    });
});

Route::group(['prefix' => 'settings'], function () {
    Route::controller(SettingController::class)->group(function () {
        Route::get('all', 'all');
    });
});

Route::group(['prefix' => 'infos'], function () {
    Route::controller(InfoController::class)->group(function () {
        Route::get('all', 'all');
    });
});

Route::group(['middleware' => 'auth:center_api'], function () {

    Route::group(['prefix' => 'auth'], function () {
        Route::controller(AuthController::class)->group(function () {
            Route::get('logout', 'logout');
            Route::post('delete', 'delete');
        });
    });

    Route::group(['prefix' => 'profile'], function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get('get', 'get');
            Route::post('update', 'update');
            Route::post('change-language', 'changeLanguage');
            Route::post('change-password', 'changePassword');
        });
    });

    Route::group(['prefix' => 'notifications'], function () {
        Route::controller(NotificationController::class)->group(function () {
            Route::get('get', 'get')->can('VIEW_NOTIFICATIONS');
        });
    });

    Route::group(['prefix' => 'roles'], function () {
        Route::controller(RoleController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_CENTERUSERROLES');
        });
    });

    Route::group(['prefix' => 'center_users'], function () {
        Route::controller(CenterUserController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_CENTERUSERS');
            Route::get('find', 'find')->can('VIEW_CENTERUSERS');
            Route::post('add', 'add')->can('CREATE_CENTERUSERS');
            Route::post('edit', 'edit')->can('UPDATE_CENTERUSERS');
            Route::delete('delete', 'delete')->can('DELETE_CENTERUSERS');
        });
    });

    Route::group(['prefix' => 'users'], function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_USERS');
            Route::get('find', 'find')->can('VIEW_USERS');
            Route::post('add', 'add')->can('CREATE_USERS');
            Route::post('edit', 'edit')->can('UPDATE_USERS');
            Route::delete('delete', 'delete')->can('DELETE_USERS');
        });
    });

    Route::group(['prefix' => 'branches'], function () {
        Route::controller(BranchController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_BRANCHES');
            Route::get('find', 'find')->can('VIEW_BRANCHES');
            Route::post('add', 'add')->can('CREATE_BRANCHES');
            Route::post('edit', 'edit')->can('UPDATE_BRANCHES');
            Route::delete('delete', 'delete')->can('DELETE_BRANCHES');
        });
    });

    Route::group(['prefix' => 'services'], function () {
        Route::controller(ServiceController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_SERVICES');
            Route::get('find', 'find')->can('VIEW_SERVICES');
            Route::post('add', 'add')->can('CREATE_SERVICES');
            Route::post('edit', 'edit')->can('UPDATE_SERVICES');
            Route::delete('delete', 'delete')->can('DELETE_SERVICES');
        });
    });

    Route::group(['prefix' => 'weeks_days'], function () {
        Route::controller(WeekDayController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_WEEKSDAYS');
            Route::post('edit', 'edit')->can('VIEW_WEEKSDAYS');
        });
    });

    Route::group(['prefix' => 'shifts'], function () {
        Route::controller(ShiftController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_SHIFTS');
            Route::get('find', 'find')->can('VIEW_SHIFTS');
            Route::post('add', 'add')->can('CREATE_SHIFTS');
            Route::post('edit', 'edit')->can('UPDATE_SHIFTS');
            Route::delete('delete', 'delete')->can('DELETE_SHIFTS');
        });
    });

    Route::group(['prefix' => 'workers'], function () {
        Route::controller(WorkerController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_WORKERS');
            Route::get('find', 'find')->can('VIEW_WORKERS');
            Route::post('add', 'add')->can('CREATE_WORKERS');
            Route::post('edit', 'edit')->can('UPDATE_WORKERS');
            Route::delete('delete', 'delete')->can('DELETE_WORKERS');
        });
    });

    Route::group(['prefix' => 'memberships'], function () {
        Route::controller(MembershipController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_MEMBERSHIPS');
            Route::get('find', 'find')->can('VIEW_MEMBERSHIPS');
            Route::post('add', 'add')->can('CREATE_MEMBERSHIPS');
            Route::post('edit', 'edit')->can('UPDATE_MEMBERSHIPS');
            Route::delete('delete', 'delete')->can('DELETE_MEMBERSHIPS');
        });
    });

    Route::group(['prefix' => 'discounts'], function () {
        Route::controller(DiscountController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_DISCOUNTS');
            Route::get('find', 'find')->can('VIEW_DISCOUNTS');
            Route::post('add', 'add')->can('CREATE_DISCOUNTS');
            Route::post('edit', 'edit')->can('UPDATE_DISCOUNTS');
            Route::delete('delete', 'delete')->can('DELETE_DISCOUNTS');
        });
    });

    Route::group(['prefix' => 'wallets'], function () {
        Route::controller(WalletController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_WALLETS');
            Route::get('find', 'find')->can('VIEW_WALLETS');
            Route::post('add', 'add')->can('CREATE_WALLETS');
            Route::post('edit', 'edit')->can('UPDATE_WALLETS');
            Route::delete('delete', 'delete')->can('DELETE_WALLETS');
        });
    });

    Route::group(['prefix' => 'users-wallets'], function () {
        Route::controller(UserWalletController::class)->group(function () {
            Route::get('show-users', 'showUsers')->can('VIEW_USERS_WALLETS');
            Route::post('add-user', 'addUser')->can('CREATE_USERS_WALLETS');
            Route::get('print-invoice', 'printInvoice')->can('VIEW_USERS_WALLETS');
        });
    });

    Route::group(['prefix' => 'packages'], function () {
        Route::controller(PackageController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_PACKAGES');
            Route::get('find', 'find')->can('VIEW_PACKAGES');
            Route::post('add', 'add')->can('CREATE_PACKAGES');
            Route::post('edit', 'edit')->can('UPDATE_PACKAGES');
            Route::delete('delete', 'delete')->can('DELETE_PACKAGES');
        });
    });

    Route::group(['prefix' => 'bookings'], function () {
        Route::controller(BookingController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_BOOKINGS');
            Route::get('check', 'check')->can('CREATE_BOOKINGS');
            Route::post('add', 'add')->can('CREATE_BOOKINGS');
            Route::delete('delete', 'delete')->can('DELETE_BOOKINGS');
            Route::get('print-invoice', 'printInvoice')->can('VIEW_BOOKINGS');
        });
    });

    Route::group(['prefix' => 'booking_with_tips'], function () {
        Route::controller(BookingWithTipsController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_BOOKING_WITH_TIPS');
            Route::post('add', 'add')->can('CREATE_BOOKING_WITH_TIPS');
            Route::post('edit', 'edit')->can('UPDATE_BOOKING_WITH_TIPS');
            Route::delete('delete', 'delete')->can('DELETE_BOOKING_WITH_TIPS');
            Route::get('print-invoice', 'printInvoice')->can('VIEW_BOOKING_WITH_TIPS');
        });
    });

    Route::group(['prefix' => 'products'], function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_PRODUCTS');
            Route::get('find', 'find')->can('VIEW_PRODUCTS');
            Route::post('add', 'add')->can('CREATE_PRODUCTS');
            Route::post('edit', 'edit')->can('UPDATE_PRODUCTS');
            Route::delete('delete', 'delete')->can('DELETE_PRODUCTS');
        });
    });

    Route::group(['prefix' => 'buyproducts'], function () {
        Route::controller(BuyProductController::class)->group(function () {
            Route::get('all', 'all')->can('VIEW_BUYPRODUCTS');
            Route::post('add', 'add')->can('CREATE_BUYPRODUCTS');
            Route::delete('delete', 'delete')->can('DELETE_BUYPRODUCTS');
            Route::get('print-invoice', 'printInvoice')->can('VIEW_BUYPRODUCTS');
        });
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::controller(DailyReportController::class)->group(function () {
            Route::get('daily-report', 'daily_report')->can('VIEW_DAILY_REPORTS');
        });
        Route::controller(SalesReportController::class)->group(function () {
            Route::get('sales-report', 'sales')->can('VIEW_SALES_REPORTS');
        });
        Route::controller(StaffReportController::class)->group(function () {
            Route::get('staff-report', 'staff')->can('VIEW_STAFF_REPORTS');
        });
        Route::controller(CommissionReportController::class)->group(function () {
            Route::get('commission-report', 'commissions')->can('VIEW_COMMISSION_REPORTS');;
        });
        Route::controller(TipsReportController::class)->group(function () {
            Route::get('get-workers-by-branch', 'getWorkersByBranch')->can('VIEW_TIPS_REPORTS');
            Route::get('tips-report', 'tips')->can('VIEW_TIPS_REPORTS');
            Route::get('print-invoice', 'printInvoice')->can('VIEW_TIPS_REPORTS');
        });
    });

});
