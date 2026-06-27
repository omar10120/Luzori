<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class MainSettingsController extends Controller
{
    private CRUDService $crudService;
    private $model = 'MainSetting';
    private $plural = 'mainsettings';
    private $indexRoute;
    private $updateOrCreateRoute;
    // public function __construct(CRUDService $crudService)
    // {
    //     $this->crudService = $crudService;
    //     $this->indexRoute = 'center_user.' . $this->plural . '.index';
    //     $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    // }
    
    public function index(Request $request)
    {
        $can = 'VIEW_SETTINGS';
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }
        
        $title = __('locale.settings');
        $menu = __('locale.mainsettings');
        return view("CenterUser.SubViews.mainSettings.index", compact('title', 'menu'));
    }
}
