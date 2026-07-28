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
     * Fetch all global categories with pagination.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);
        
        $paginator = GlobalCategory::on('central')
            ->orderBy('id')
            ->paginate($perPage);

        // Build paginated response structure
        $data = [
            'data' => GlobalCategoryResource::collection($paginator->items()),
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];

        return MyHelper::responseJSON(
            __('api.doneSuccessfully'),
            Response::HTTP_OK,
            $data
        );
    }
}