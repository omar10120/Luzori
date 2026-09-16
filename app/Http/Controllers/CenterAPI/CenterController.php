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
     * Fetch a single approved center by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
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
                $center->about_us = (new \App\Services\PageService())->aboutUs();
                $center->infos = Info::all();  

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

            $center->is_favorite = $this->isFavoriteForRequest($request, (int) $center->id);

            $stats = CenterReview::where('center_id', $center->id)
                ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as avg_rating')
                ->first();
            $center->avg_rating = ($stats && $stats->reviews_count)
                ? round((float) $stats->avg_rating, 1)
                : null;
            $center->reviews_count = (int) ($stats->reviews_count ?? 0);

            $myReview = null;
            $user = $request->user();
            if ($user instanceof AppUser) {
                $myReview = CenterReview::where('user_id', $user->id)
                    ->where('center_id', $center->id)
                    ->first(['id', 'rating', 'comment']);
            }
            $center->has_review = (bool) $myReview;
            $center->my_review = $myReview ? [
                'id' => (int) $myReview->id,
                'rating' => (int) $myReview->rating,
                'comment' => $myReview->comment,
            ] : null;

            // Same include pattern: ?include=reviews
            $includeRaw = $request->input('include', '');
            $includes = is_array($includeRaw)
                ? $includeRaw
                : explode(',', (string) $includeRaw);
            $includes = array_map(fn ($v) => strtolower(trim((string) $v)), $includes);
            if (in_array('reviews', $includes, true)) {
                $center->setRelation(
                    'reviews',
                    CenterReview::with('user')
                        ->where('center_id', $center->id)
                        ->latest()
                        ->get()
                );
            }

            $payload = json_decode(CenterResource::make($center)->toJson(), true);
            $payload['is_favorite'] = (bool) $center->is_favorite;
            $payload['avg_rating'] = $center->avg_rating;
            $payload['reviews_count'] = $center->reviews_count;
            $payload['has_review'] = (bool) $center->has_review;
            $payload['my_review'] = $center->my_review;

            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $payload);
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
