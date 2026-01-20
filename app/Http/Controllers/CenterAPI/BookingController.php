<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\CRUDService;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\Booking\BookingRequest;
use App\Http\Requests\CenterAPI\Booking\CheckBookingIdRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingUserResource;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\PrintBookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BookingController extends Controller
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

    public function all()
    {
        $relations = ['branch', 'user', 'details' => function ($q) {
            $q->with(['service' => function ($q1) {
                $q1->with(['translation']);
            }]);
        }];

        $items = $this->crudService->paginate($this->model, $relations, 0);
        if ($items) {
            $paginationData = PaginateDateResource::make($items);
            $items = BookingResource::collection($items);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function check(Request $request, BookingService $bookingService)
    {
        $item = $bookingService->check($request->mobile, $reason);
        if ($item) {
            $item = BookingUserResource::make($item);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $item);
        } else {
            if ($reason == 'USER_NOT_FOUND') {
                return MyHelper::responseJSON(__('api.userNotFound'), Response::HTTP_BAD_REQUEST);
            } else {
                return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function add(BookingRequest $request, BookingService $bookingService)
    {
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
            $item = BookingResource::make($item);
            return MyHelper::responseJSON(__('api.addSuccessfully'), Response::HTTP_CREATED, $item);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(CheckBookingIdRequest $request)
    {
        $item = $this->crudService->delete($this->model, $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printInvoice(CheckBookingIdRequest $request)
    {
        $booking = Booking::with(['created_by_user', 'user', 'wallet', 'details' => function ($q) {
            $q->with(['service' => function ($q) {
                $q->with(['translation']);
            }]);
        }])->find($request->id);

        if ($booking) {
            $booking = PrintBookingResource::make($booking);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $booking);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
