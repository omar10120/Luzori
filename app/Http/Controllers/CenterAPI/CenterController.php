<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Helpers\MyHelper;
use App\Http\Resources\CenterResource;
use App\Http\Requests\CenterAPI\RegisterRequest;
use App\Services\CenterService;
use App\Models\Center;
use App\Models\Branch;
use App\Models\CategoryService;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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

        if ($request->filled('rate')) {
            $rate = trim($request->query('rate'), '"');
            $query->where('rate', $rate);
        }

        $centers = $query->get();

        if ($centers->count() > 0) {
            $centers = CenterResource::collection($centers);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $centers);
        } else {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Fetch a single approved center by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $center = Center::where('status', 'approve')->find($id);

        if ($center) {
            // Switch to center's database to fetch nested data
            if ($center->database) {
                Config::set('database.connections.mysql.database', $center->database);
                DB::reconnect();

                // Fetch data from the switched mysql connection
                $center->branches = Branch::all();
                $center->categories = CategoryService::with('services.workers.vacations')->get();
                $center->services = Service::with('workers.vacations')->where('is_top', true)->get();
              
            }

            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, CenterResource::make($center));
        }

        return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
    }

    /**
     * Register a new center.
     * 
     * @param RegisterRequest $request
     * @param CenterService $centerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request, CenterService $centerService)
    {
        $data = $request->validated();
        $data['role'] = 'Super Admin';
        $data['status'] = 'pending';

        $center = $centerService->add($data);

        if ($center) {
            return MyHelper::responseJSON(__('api.registerSuccessfully'), Response::HTTP_CREATED, CenterResource::make($center));
        }

        return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
