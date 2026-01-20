<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function cp()
    {
        $usersCount = 10;
        return view('Admin.SubViews.cp', [
            'usersCount' => $usersCount,
        ]);
    }
}
