<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\BuyProduct\BuyProductRequest;
use App\Http\Requests\CenterAPI\BuyProduct\CheckBuyProductIdRequest;
use App\Http\Resources\BuyProductResource;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\PrintBuyProductResource;
use App\Models\BuyProduct;
use Illuminate\Http\Response;

class BuyProductController extends Controller
{
    private CRUDService $crudService;
    private $model = 'BuyProduct';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all()
    {
        $relations = ['worker', 'details' => function ($q) {
            $q->with(['product' => function ($q1) {
                $q1->with(['translation']);
            }]);
        }];

        $items = $this->crudService->paginate($this->model, $relations, 0);
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = BuyProductResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(BuyProductRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated(), true);
        if ($item) {
            $item = BuyProductResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckBuyProductIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printInvoice(CheckBuyProductIdRequest $request)
    {
        $buyProduct = BuyProduct::with(['created_by_user', 'details' => function ($q) {
            $q->with(['product' => function ($q) {
                $q->with(['translation']);
            }]);
        }])->findOrFail($request->id);

        if ($buyProduct) {
            $buyProduct = PrintBuyProductResource::make($buyProduct);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $buyProduct);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
