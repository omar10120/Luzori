<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\BookingWithTipsDataTable;
use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\BookingWithTipsRequest;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\Worker;
use App\Services\BookingWithTipsService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class BookingWithTipsController extends Controller
{
    private CRUDService $crudService;
    private $plural = 'booking_with_tips';
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

    public function index(BookingWithTipsDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
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
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = null;
        if ($request->id) {
            $item = $this->crudService->find('Booking', $request->id, []);
        }

        if (is_null($item)) {
            $title = __('general.add');
            $requestUrl = route($this->updateOrCreateRoute);
        } else {
            $title = __('general.edit');
            $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);
        }
        $branches = Branch::with(['translation'])->get();
        $paymentMethods = \App\Models\PaymentMethod::forTips()->get();
        $view = 'CenterUser.SubViews.BookingWithTips.index';
        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link', 'branches', 'paymentMethods'));
    }

    public function updateOrCreate(BookingWithTipsRequest $request, BookingWithTipsService $bookingWithTipsService)
    {
        $responseCode = Response::HTTP_OK;
        if (isset($request->id)) {
            $can = 'UPDATE_' . Str::upper($this->plural);
        } else {
            $responseCode = Response::HTTP_CREATED;
            $can = 'CREATE_' . Str::upper($this->plural);
        }
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        if (isset($request->id)) {
            $newRequest = $request->only(
                'id',
                'date_time'
            );
            $item = $bookingWithTipsService->edit($newRequest);
        } else {
            $newRequest = $request->only(
                'branch_id',
                'worker_id',
                'date_time',
                'tip',
                'payment_type'
            );
            $item = $bookingWithTipsService->add($newRequest);
        }
        if ($item) {
            // return MyHelper::responseJSON(__('admin.operation_done_successfully'), $responseCode, $item);
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route($this->indexRoute));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getWorkers(Request $request)
    {
        $workers = Worker::where('branch_id', $request->branch_id)->get();
        return response()->json($workers);
    }

    public function print(Request $request)
    {
        $booking = Booking::with(['created_by_user', 'user', 'wallet', 'details' => function ($q) {
            $q->with(['service' => function ($q) {
                $q->with(['translation']);
            }]);
        }])->withTrashed()->find($request->id);

        $options = [
            'format' => [80, 160], // Custom paper size (width, height) in points
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

        $view = 'CenterUser.SubViews.Booking.print';
        $pdf = Pdf::loadView($view, compact('booking', 'template'), [], $options);

        return $pdf->stream('booking_with_tips.pdf');
    }
}
