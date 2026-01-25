<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\WeekDayDataTable;
use App\Enums\DayStatusEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\WeekDay;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class WeekDayController extends Controller
{
    private $plural = 'weeksdays';

    public function index(WeekDayDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
    }

    public function changeStatus(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $weekday = WeekDay::find($request->id);
        if ($weekday->status->value == DayStatusEnum::OPEN->value) {
            $weekday->update([
                'status' => DayStatusEnum::CLOSED->value
            ]);
        } else {
            $weekday->update([
                'status' => DayStatusEnum::OPEN->value
            ]);
        }

        if ($weekday) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.weeksdays.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
