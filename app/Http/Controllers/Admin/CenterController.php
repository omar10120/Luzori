<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\CenterDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CenterRequest;
use App\Models\Center;
use App\Services\CenterService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CenterController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Center';
    private $plural = 'centers';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(CenterDataTable $dataTable)
    {
        $title = __('locale.' . $this->plural);
        return $dataTable->render("Admin.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('admin')->user()->can($can)) {
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $relations = ['media'];
            $item = $this->crudService->find($this->model, $request->id, $relations);
        }

        $roles = Role::where('guard_name', 'center')->get();
        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }

        $view = 'Admin.SubViews.' . $this->model . '.index';
        return view($view, compact('item', 'roles', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(CenterRequest $request, CenterService $centerService)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('admin')->user()->can($can)) {
            return abort(401);
        }

        
        if (isset($request->id)) {
            $item = $centerService->edit($request->validated());
        } else {
            $item = $centerService->add($request->validated());
        }
        if ($item) {
            return MyHelper::responseJSON(__('admin.operation_done_successfully'), $responseCode, $item);
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function permissions(Request $request)
    {
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('general.permissions');

        $allPermissions = Permission::where('guard_name', 'center')->get()->select('name', 'name_ar', 'group')->groupBy('group');

        $id = $request->id;
        $center = Center::find($id);
        $dbName = $center->database;
        Config::set('database.connections.mysql.database', $dbName);
        DB::reconnect();

        $centerPermissions = Permission::all()->select('name', 'name_ar', 'group');
        $requestUrl = route('admin.centers.update.permissions', ['id' => $id]);

        Config::set('database.connections.mysql.database', env('DB_DATABASE'));
        DB::reconnect();

        $view = 'Admin.SubViews.' . $this->model . '.permissions';
        return view($view, compact('allPermissions', 'centerPermissions', 'menu', 'menu_link', 'title', 'requestUrl', 'id'));
    }

    public function updatePermissions(Request $request)
    {
        $center = Center::find($request->id);
        $dbName = $center->database;
        Config::set('database.connections.mysql.database', $dbName);
        DB::reconnect();

        $permissions = $request->permissions;
        $centerPermissions = Permission::all()->pluck('name')->toArray();

        $permissionsToAdd = array_diff($permissions, $centerPermissions);
        $permissionsToRemove = array_diff($centerPermissions, $permissions);

        foreach ($permissionsToRemove as $permission) {
            Permission::where('name', $permission)->delete();
        }

        Config::set('database.connections.mysql.database', env('DB_DATABASE'));
        DB::reconnect();
        $permissionToAddNew = [];
        foreach ($permissionsToAdd as $permission) {
            $perm = Permission::where('name', $permission)->first();
            $permissionToAddNew[] = [
                'name' => $perm->name,
                'name_ar' => $perm->name_ar,
                'guard_name' => 'center_api',
                'group' => $perm->group,
            ];
        }

        Config::set('database.connections.mysql.database', $dbName);
        DB::reconnect();
        foreach ($permissionToAddNew as $permission) {
            Permission::create($permission);
        }

        return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('admin.centers.index'));
    }

    /**
     * Manually register a center as a MyFatoorah supplier (admin-triggered).
     * Uses static commission/deposit values as agreed.
     */
    public function createSupplier(Request $request, $id)
    {
        $center = Center::find($id);

        if (!$center) {
            return MyHelper::responseJSON('Center not found.', Response::HTTP_NOT_FOUND);
        }

        if ($center->is_supplier) {
            return MyHelper::responseJSON('Center is already registered as a supplier.', Response::HTTP_OK, [
                'supplier_code' => $center->supplier_code,
            ]);
        }

        if (empty($center->iban) || empty($center->BankAccountHolderName) || empty($center->BusinessName) || empty($center->BankAccount)) {
            $errMsg = app()->getLocale() == 'ar'
                ? 'لا يمكن إنشاء مورد. المركز لم يقم بتعبئة بياناته البنكية بعد.'
                : 'Cannot create supplier. The center has not filled in their bank details yet.';
            return MyHelper::responseJSON($errMsg, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $payload = [
                'SupplierName'          => $center->name,
                'Mobile'                => $center->phone ?? '',
                'Email'                 => $center->email ?? '',
                'CommissionValue'       => 1,
                'CommissionPercentage'  => 3,
                'DepositTerms'          => 'Daily',
                'BankId'                => 1,
                'Iban'                  => $center->iban ?? '',
                'IsActive'              => true,
                'BankAccount'           => $center->bank_name ?? '',
                'BankAccountHolderName' => $center->BankAccountHolderName ?? '',
                'BusinessName'          => $center->BusinessName ?? '',
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MYFATOORAH_TOKEN'),
                'Content-Type'  => 'application/json',
            ])->post(rtrim(env('MYFATOORAH_BASE_URL'), '/') . '/v2/CreateSupplier', $payload);

            Log::info('Admin CreateSupplier response', [
                'center'   => $center->domain,
                'response' => $response->json(),
            ]);

            if ($response->successful() && ($response->json('IsSuccess') === true)) {
                $data = $response->json('Data');
                $center->update([
                    'supplier_code'  => $data['SupplierCode'],
                    'supplier_email' => $data['SupplierEmail'],
                    'supplier_date'  => $data['Date'],
                    'is_supplier'    => true,
                ]);

                return MyHelper::responseJSON(
                    __('admin.operation_done_successfully'),
                    Response::HTTP_OK,
                    ['supplier_code' => $data['SupplierCode']]
                );
            }

            $apiMessage = $response->json('Message') ?? __('admin.an_error_occurred');
            return MyHelper::responseJSON($apiMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            Log::error('Admin CreateSupplier exception: ' . $e->getMessage(), ['center' => $center->domain]);
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
