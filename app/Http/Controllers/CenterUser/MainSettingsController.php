<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MainSettingsController extends Controller
{
    public function index(Request $request)
    {
        $title = __('locale.settings') ?? 'Settings';
        $menu = $title;
        $menu_link = route('center_user.mainsettings.index');

        $view = 'CenterUser.SubViews.mainSettings.index';
        return view($view, compact('title', 'menu', 'menu_link'));
    }
}
