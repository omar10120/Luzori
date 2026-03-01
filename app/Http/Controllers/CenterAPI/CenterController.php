<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Helpers\MyHelper;
use App\Http\Resources\CenterResource;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CenterController extends Controller
{
    /**
     * Fetch all approved centers.
     * Optional 'rate' query parameter to filter centers by their status.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Center::where('status', 'approve');

        if ($request->has('rate')) {
            $query->where('rate', $request->rate);
        }

        $centers = $query->get();

        if ($centers->count() > 0) {
            $centers = CenterResource::collection($centers);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $centers);
        } else {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }
    }
}
