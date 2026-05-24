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
use App\Models\Package;
use App\Models\UserPackage;
use App\Models\UserUsedPackage;
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
    public function index(Request $request, CenterService $centerService)
    {
        $filteredCenters = $centerService->getFilteredCenters($request);
        

        if (count($filteredCenters) > 0) {
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $filteredCenters);
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
        $center = Center::where('status', 'approve')
            ->where(function ($q) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>', now());
            })
            ->with('globalCategories')
            ->find($id);

        if ($center) {
            // Switch to center's database to fetch nested data
            if ($center->database) {
                Config::set('database.connections.mysql.database', $center->database);
                DB::reconnect();

                // Fetch data from the switched mysql connection
                $center->branches = Branch::all();
                $center->categories = CategoryService::with('services.workers.vacations')->get();
                $center->services = Service::with('workers.vacations')->where('is_top', true)->get();
                $center->packages = Package::all();
                
                $userId = auth('center_api')->id();
                if ($userId) {
                    $center->user_packages = UserPackage::where('user_id', $userId)
                        ->with(['package.translation'])
                        ->get();
                    
                    $center->user_used_packages = UserUsedPackage::where('user_id', $userId)
                        ->with(['service.translation'])
                        ->get();
                }
              
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
        $data['status'] = 'approve';

        $center = $centerService->add($data);

        if ($center) {
            return MyHelper::responseJSON(__('api.registerSuccessfully'), Response::HTTP_CREATED, CenterResource::make($center));
        }

        return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    
}
