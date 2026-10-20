<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\GlobalProduct;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function cp()
    {
        $productsCount = GlobalProduct::query()->count();
        $servicesCount = Service::query()->count();
        $usersCount = User::query()->count();
        $centersCount = Center::query()->count();

        $months = [];
        $usersTrend = [];
        $centersTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->translatedFormat('M');

            $usersTrend[] = User::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $centersTrend[] = Center::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return view('Admin.SubViews.cp', [
            'productsCount' => $productsCount,
            'servicesCount' => $servicesCount,
            'usersCount' => $usersCount,
            'centersCount' => $centersCount,
            'chartMonths' => $months,
            'usersTrend' => $usersTrend,
            'centersTrend' => $centersTrend,
        ]);
    }
}
