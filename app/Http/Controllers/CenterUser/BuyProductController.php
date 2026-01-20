<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\BuyProductDataTable;
use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\BuyProductRequest;
use App\Models\BuyProduct;
use App\Models\Setting;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class BuyProductController extends Controller
{
    private CRUDService $crudService;
    private $model = 'BuyProduct';
    private $plural = 'buyproducts';
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

    public function index(BuyProductDataTable $dataTable)
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
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $title = __('general.add');
        $requestUrl = route($this->updateOrCreateRoute);

        $products = $this->crudService->all('Product', ['translation'], 0);
        $workers = $this->crudService->all('Worker', [], 0);
        $paymentMethods = \App\Models\PaymentMethod::forProduct()->orWhereJsonContains('types', 'general')->get();

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('products', 'workers', 'paymentMethods', 'requestUrl', 'title', 'menu', 'menu_link'));
    }

    public function updateOrCreate(BuyProductRequest $request)
    {
        $responseCode = Response::HTTP_CREATED;
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(401);
        }

        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);

        if ($item) {
            // return MyHelper::responseJSON(__('admin.operation_done_successfully'), $responseCode, $item);
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.buyproducts.index'));
            
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function print(Request $request)
    {
        $result = BuyProduct::with("created_by_user")->withTrashed()->findOrFail($request->id);

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

        $view = 'CenterUser.SubViews.' . $this->model . '.print';
        $pdf = Pdf::loadView($view, compact('result', 'template'), [], $options);

        return $pdf->stream('buy_product.pdf');
    }
}
