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
        // 1. Safely get per_page (Default to 15, max out at 100 to prevent crashes)
        $perPage = min((int) $request->input('per_page', 15), 100);
        
        // 2. Add ->withQueryString() so your filters aren't lost when changing pages
        $paginator = GlobalCategory::on('central')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        // 3. Use the API Resource Collection directly. 
        // Laravel automatically formats the data, links, and meta arrays perfectly.
        $data = GlobalCategoryResource::collection($paginator);

        return MyHelper::responseJSON(
            __('api.doneSuccessfully'),
            Response::HTTP_OK,
            $data
        );
    }
}