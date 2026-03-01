<?php

namespace App\Http\Controllers\CenterAPI;

use App\Datatables\CenterUser\CategoryServiceDataTable ;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\CategoryRequest;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\CategoryService;

class CategoryServiceController extends Controller
{
    private CRUDService $crudService;
    private $model = 'CategoryService';
    private $plural = 'CategoryService';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(CategoryServiceDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $relations = ['translation', 'parent.translation'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        // Get all categories for parent selection (excluding current item if editing)
        // Load categories with their children recursively for hierarchical display
        $excludeId = $request->id ?? 0;
        $categories = CategoryService::with(['translation'])
            ->where('id', '!=', $excludeId)
            ->whereNull('parent_id') // Only top-level categories
            ->get();
        
        // Load children recursively for each category
        $categories->load(['children' => function($query) use ($excludeId) {
            $query->where('id', '!=', $excludeId)->with('translation');
        }]);
        
        // Recursively load nested children
        foreach ($categories as $category) {
            $this->loadChildrenRecursively($category, $excludeId);
        }

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link', 'categories'));
    }

    public function updateOrCreate(CategoryRequest $request)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $newRequest = $request->only(
            'id',
            'ar',
            'en',
            'parent_id',
        );
        $item = $this->crudService->updateOrCreate($this->model, $newRequest, true);
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.categories.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Recursively load children categories
     */
    private function loadChildrenRecursively($category, $excludeId)
    {
        if ($category->children) {
            foreach ($category->children as $child) {
                $child->load(['children' => function($query) use ($excludeId) {
                    $query->where('id', '!=', $excludeId)->with('translation');
                }]);
                $this->loadChildrenRecursively($child, $excludeId);
            }
        }
    }
}
