<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Services\PageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PageController extends Controller
{
    private PageService $pageService;
    private $model = 'Page';
    private $plural = 'pages';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param PageService $pageService
     */
    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $relations = ['translation'];
        $item = $this->pageService->all();
        $title = __('general.edit');
        $requestUrl = route($this->updateOrCreateRoute, ['id' => 1]);

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(PageRequest $request)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $item = $this->pageService->edit($request->only('ar', 'en'));
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.pages.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
