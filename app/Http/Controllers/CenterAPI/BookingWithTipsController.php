<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\BookingWithTips\BookingWithTipsRequest;
use App\Http\Requests\CenterAPI\BookingWithTips\CheckBookingWithTipsIdRequest;
use App\Http\Resources\BookingWithTipsResource;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\PrintBookingWithTipsResource;
use App\Models\Booking;
use App\Services\BookingWithTipsService;
use Illuminate\Http\Response;

class BookingWithTipsController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Booking';

    /**
     * @param CRUDService $crudService
     */
    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all(BookingWithTipsService $bookingWithTipsService)
    {
        $items = $bookingWithTipsService->paginate();
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = BookingWithTipsResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(BookingWithTipsRequest $request, BookingWithTipsService $bookingWithTipsService)
    {
        $newRequest = $request->only(
            'branch_id',
            'worker_id',
            'date_time',
            'tip',
            'payment_type'
        );
        $item = $bookingWithTipsService->add($newRequest);
        if ($item) {
            $item = BookingWithTipsResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(CheckBookingWithTipsIdRequest $request, BookingWithTipsService $bookingWithTipsService)
    {
        $newRequest = $request->only(
            'id',
            'date_time'
        );
        $item = $bookingWithTipsService->edit($newRequest);
        if ($item) {
            $item = BookingWithTipsResource::make($item);
            return MyHelper::responseJSON(__('api.editSuccessfully'), Response::HTTP_OK, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckBookingWithTipsIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printInvoice(CheckBookingWithTipsIdRequest $request)
    {
        $booking = Booking::with(['created_by_user', 'user', 'details'])->find($request->id);

        if ($booking) {
            $booking = PrintBookingWithTipsResource::make($booking);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $booking);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
