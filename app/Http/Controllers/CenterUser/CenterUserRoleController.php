<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\CenterUserRoleDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\RoleRequest;
use App\Models\Setting;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class CenterUserRoleController extends Controller
{
    private RoleService $roleService;
    private $model = 'CenterUserRole';
    private $plural = 'centeruserroles';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param RoleService $roleService
     */
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(CenterUserRoleDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render('CenterUser.SubViews.CenterUserRole.list', compact('title'));
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

        $permissions = Permission::where('guard_name', 'center_api')->get();
        $permissions = $permissions->groupBy('group');

        $item = null;
        if ($request->id) {
            $item = $this->roleService->find($request->id);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'permissions', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(RoleRequest $request)
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

        if (isset($request->id)) {
            $item = $this->roleService->edit($request->validated());
        } else {
            $item = $this->roleService->add($request->validated());
        }

        if ($item) {
            $this->saveCommissionRates($request);
            // return MyHelper::responseJSON(__('admin.operation_done_successfully'), $responseCode, $item);
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route($this->indexRoute));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function saveCommissionRates(Request $request): void
    {
        if (!$request->exists('commission_rates_present')) {
            return;
        }

        $percentInput = $request->input('commission_rates', []);
        $fixedInput = $request->input('commission_fixed', []);
        $clean = [];
        foreach (['booking', 'coupon', 'product'] as $kind) {
            $clean[$kind] = [
                'percentage' => $this->cleanCommissionSteps($percentInput[$kind] ?? []),
                'fixed' => $this->cleanCommissionSteps($fixedInput[$kind] ?? []),
            ];
        }

        Setting::query()->updateOrCreate(
            ['key' => 'commission_percentage_rates'],
            ['value' => json_encode($clean)]
        );
    }

    private function cleanCommissionSteps(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => round((float) $value, 2))
            ->filter(fn ($value) => $value >= 1 && $value <= 100 && abs(($value * 2) - round($value * 2)) < 0.001)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
