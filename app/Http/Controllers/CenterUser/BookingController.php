<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\BookingDataTable;
use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\BookingRequest;
use App\Models\Discount;
use App\Models\Worker;
use App\Models\User;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\Setting;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class BookingController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Booking';
    private $plural = 'bookings';
    private $indexRoute;
    private $updateOrCreateRoute;

    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(BookingDataTable $dataTable)
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
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $title = __('general.add');
        $requestUrl = route($this->updateOrCreateRoute);

        $services = Service::with(['translation'])->get();
        $workers = Worker::all();
        $discounts = Discount::all();
        $paymentMethods = \App\Models\PaymentMethod::forBooking()->orWhereJsonContains('types', 'general')->get();

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('requestUrl', 'title', 'menu', 'menu_link', 'services', 'workers', 'discounts', 'paymentMethods'));
    }

    public function updateOrCreate(BookingRequest $request, BookingService $bookingService)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $newRequest = $request->only(
            'full_name',
            'mobile',
            'discount_id',
            'wallet_id',
            'membership_id',
            'payment_type',
            'service',
        );
        $item = $bookingService->add($newRequest);
        if ($item) {
            // return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, $item);
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.bookings.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getServicesByUser(Request $request)
    {
        $user = User::with(['memberships', 'wallets', 'services' => function ($q) {
            $q->with('service');
        }])->where('phone', $request->user_phone)->first();

        if ($user) {
            $services = $user->services->groupBy('service_id');
            $wallets = $user->wallets()->with(['wallet'])->join('wallets', 'users_wallets.wallet_id', '=', 'wallets.id')->get();
            $memberships = $user->memberships()->get();

            return response()->json([
                'status' => true,
                'user' => $user,
                'services' => $services,
                'wallets' => $wallets,
                'memberships' => $memberships
            ]);
        } else {
            return response()->json(['status' => false]);
        }
    }

    public function getUsersByBranch(Request $request)
    {
        $users = Worker::where('branch_id', $request->branch_id)->get(['id', 'name']);
        return response()->json(['users' => $users]);
    }

    public function print(Request $request)
    {
        $booking = Booking::with(['created_by_user', 'user', 'wallet', 'details' => function ($q) {
            $q->with(['service' => function ($q) {
                $q->with(['translation']);
            }]);
        }])->withTrashed()->findOrFail($request->id);

        $options = [
            'format' => [80, 200], // Custom paper size (width, height) in points
            'orientation' => 'portrait', // or 'landscape'
            'margin-top' => 10,
            'margin-bottom' => 10,
            'margin-left' => 10,
            'margin-right' => 10,
        ];

        $invoice_info = Setting::where('key', SettingEnum::invoice_info->value)->first()->value;
        $template = (string)view('CenterUser.SubViews.Report.template.invoice_info', compact(
            'invoice_info',
        ));

        $view = 'CenterUser.SubViews.' . $this->model . '.print';
        $pdf = Pdf::loadView($view, compact('booking', 'template'), [], $options);
        return $pdf->stream('booking.pdf');
    }
}
