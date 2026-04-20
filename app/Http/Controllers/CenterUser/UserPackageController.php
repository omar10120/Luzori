<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\UserPackageRequest;
use App\Services\CRUDService;
use App\Models\Package;
use App\Models\Worker;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class UserPackageController extends Controller
{
    private CRUDService $crudService;
    private $model = 'UserPackage';
    private $plural = 'users_packages';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.packages.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function create(Request $request)
    {
        $title = __('locale.add_users_to');
        $menu = __('locale.packages');

        $menu_link = route($this->indexRoute);
        $requestUrl = route($this->updateOrCreateRoute);

        $package = Package::find($request->id);
        $users = User::all();
        
        $paymentMethods = \App\Models\PaymentMethod::forWallet()->get();

        $view = 'CenterUser.SubViews.Package.add_user_package';
        return view($view, compact('package', 'requestUrl', 'title', 'menu', 'menu_link', 'users', 'paymentMethods'));
    }

    public function updateOrCreate(UserPackageRequest $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $package = Package::find($request->package_id);
        $user = User::find($request->user_id);

        if ($request->package_type == 'Wallet') {
            if ($user->wallet < $package->price) {
                return MyHelper::responseJSON(__('admin.insufficient_balance'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $user->update([
                'wallet' => $user->wallet - $package->price
            ]);
        }

        $newRequest = $request->validated();
        $newRequest['price'] = $package->price;
        $newRequest['status'] = 'active';
        $newRequest['created_by'] = auth('center_user')->id();

        $item = $this->crudService->updateOrCreate($this->model, $newRequest);

        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.packages.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
