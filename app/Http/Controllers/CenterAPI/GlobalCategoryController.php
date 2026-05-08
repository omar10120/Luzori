<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Helpers\MyHelper;
use App\Models\GlobalCategory;
use App\Http\Resources\GlobalCategoryResource;
use Illuminate\Http\Response;

class GlobalCategoryController extends Controller
{
    /**
     * Fetch all global categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = GlobalCategory::on('central')->orderBy('id')->get();

        if ($categories->isNotEmpty()) {
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, GlobalCategoryResource::collection($categories));
        }

        return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
    }
}
