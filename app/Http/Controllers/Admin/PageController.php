<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Services\PageService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(Request $request)
    {
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $relations = ['translation'];
        $item = $this->pageService->all();
        $title = __('general.edit');
        $requestUrl = route($this->updateOrCreateRoute, ['id' => 1]);

        $view = 'Admin.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(PageRequest $request)
    {
        $item = $this->pageService->edit($request->only('ar', 'en'));
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('admin.pages.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
