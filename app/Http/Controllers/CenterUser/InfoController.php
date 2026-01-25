<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InfoRequest;
use App\Models\Info;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class InfoController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Info';
    private $plural = 'infos';
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

    public function index(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = $this->crudService->find($this->model, 1, [], 0);
        $title = __('general.edit');
        $requestUrl = route($this->updateOrCreateRoute, ['id' => 1]);

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(InfoRequest $request)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $item = $this->crudService->updateOrCreate($this->model, $request->validated());
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.infos.index'));
            
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateSenderEmail(Request $request)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $info = Info::query()->firstOrNew(['id' => 1]);
        $info->email = $request->input('email');
        $info->save();

        return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.infos.index'), [
            'email' => $info->email,
        ]);
    }
}
