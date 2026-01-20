<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\WeekDay\WeekDayRequest;
use App\Http\Resources\WeekDayResource;
use Illuminate\Http\Response;

class WeekDayController extends Controller
{
    private CRUDService $crudService;
    private $model = 'WeekDay';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all()
    {
        $items = $this->crudService->all($this->model, [], 0);
        if ($items) {
            $items = WeekDayResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(WeekDayRequest $request)
    {
        $item = $this->crudService->updateOrCreate($this->model, $request->validated());
        if ($item) {
            $item = WeekDayResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
