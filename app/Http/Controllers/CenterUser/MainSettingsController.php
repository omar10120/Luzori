<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class MainSettingsController extends Controller
{

    private $plural = 'settings';
    public function index(Request $request)
    {
        // $can = 'VIEW_' . Str::upper($this->plural);
        // if (!auth('center_user')->user()->can($can, 'center_api')) {
        //     return abort(403);
        // }
        
        $title = __('locale.settings');
        $menu = __('locale.mainsettings');
        return view("CenterUser.SubViews.mainSettings.index", compact('title', 'menu'));
    }
}
