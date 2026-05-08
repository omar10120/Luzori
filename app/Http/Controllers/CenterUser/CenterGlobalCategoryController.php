<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\GlobalCategory;
use App\Services\GlobalCategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CenterGlobalCategoryController extends Controller
{
    private GlobalCategoryService $globalCategoryService;
    private $indexRoute = 'center_user.global_categories.index';
    private $updateRoute = 'center_user.global_categories.updateOrCreate';

    public function __construct(GlobalCategoryService $globalCategoryService)
    {
        $this->globalCategoryService = $globalCategoryService;
    }

    /**
     * Show the form to assign global categories to the center.
     */
    public function index(Request $request)
    {
        if (!auth('center_user')->user()->can('VIEW_CATEGORIES', 'center_api')) {
            return abort(403);
        }

        $menu      = __('locale.global_categories');
        $menu_link = route($this->indexRoute);
        $title     = __('locale.global_categories');

        // Get the current center by database name (since we are in tenant context)
        $dbName = config('database.connections.mysql.database');
        $center = Center::with('globalCategories')
            ->where('database', $dbName)
            ->first();

        $allCategories    = GlobalCategory::on('central')->orderBy('name')->get();
        $selectedIds      = $center ? $center->globalCategories->pluck('id')->toArray() : [];
        $requestUrl       = route($this->updateRoute);

        return view('CenterUser.SubViews.GlobalCategories.index', compact(
            'menu', 'menu_link', 'title',
            'allCategories', 'selectedIds', 'requestUrl', 'center'
        ));
    }

    /**
     * Sync global categories for the center.
     */
    public function updateOrCreate(Request $request)
    {
        if (!auth('center_user')->user()->can('UPDATE_CATEGORIES', 'center_api')) {
            return abort(403);
        }

        $request->validate([
            'global_category_ids'   => ['nullable', 'array'],
            'global_category_ids.*' => ['exists:central.global_categories,id'],
        ]);

        $dbName = config('database.connections.mysql.database');
        $center = Center::with('globalCategories')
            ->where('database', $dbName)
            ->first();

        if (!$center) {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_NOT_FOUND);
        }

        $this->globalCategoryService->syncCenterCategories(
            $center,
            $request->input('global_category_ids', [])
        );

        return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route($this->indexRoute));
    }
}
