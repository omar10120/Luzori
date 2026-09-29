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
use App\Models\Info;
use App\Http\Resources\InfoResource;
use App\Services\InfoService;
use App\Models\UserPackage;
use App\Models\UserUsedPackage;
use App\Models\AppUser;
use App\Models\FavoriteCenter;
use App\Models\CenterReview;
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
    public function filter(Request $request, CenterService $centerService)
    {
        $filteredCenters = $centerService->getFilteredCentersDetail($request);
        
        if (count($filteredCenters) > 0) {
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $filteredCenters);
        } else {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Flat list of all services across approved centers (public, no auth).
     */
    public function services(Request $request, CenterService $centerService)
    {
        $result = $centerService->getAllCentersServices($request);

        if (count($result) > 0) {
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $result);
        }

        return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
    }

    /**
     * Fetch a single approved center by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id, CenterService $centerService)
    {
        $payload = $centerService->getCenterById($request, (int) $id);

        if ($payload === null) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $payload);
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

    private function isFavoriteForRequest(Request $request, int $centerId): bool
    {
        $user = $request->user();
        if (!$user instanceof AppUser) {
            return false;
        }

        return FavoriteCenter::where('user_id', $user->id)
            ->where('center_id', $centerId)
            ->exists();
    }
}
