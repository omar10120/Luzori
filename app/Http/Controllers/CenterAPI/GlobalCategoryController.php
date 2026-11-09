<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Helpers\MyHelper;
use App\Models\GlobalCategory;
use App\Http\Resources\GlobalCategoryResource;
use Illuminate\Http\Response;
use Illuminate\Http\Request; 

class GlobalCategoryController extends Controller
{
    /**
     * Fetch ALL global categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Use ->get() instead of ->paginate() to fetch everything
        $categories = GlobalCategory::on('central')
            ->orderBy('id')
            ->get();

        // Pass the full collection to the Resource
        $data = GlobalCategoryResource::collection($categories);

        return MyHelper::responseJSON(
            __('api.doneSuccessfully'),
            Response::HTTP_OK,
            $data
        );
    }
}