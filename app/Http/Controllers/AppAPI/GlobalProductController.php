<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Services\GlobalProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GlobalProductController extends Controller
{
    public function index(Request $request, GlobalProductService $globalProductService)
    {
        $validated = $request->validate([
            'center_id' => 'nullable|integer|exists:central.centers,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $products = $globalProductService->publicProducts(
            isset($validated['center_id']) ? (int) $validated['center_id'] : null,
            (int) ($validated['per_page'] ?? 20)
        );

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'products' => collect($products->items())->map(fn ($item) => [
                'id' => (int) $item->id,
                'center_id' => (int) $item->center_id,
                'center_name' => $item->center?->name,
                'center_domain' => $item->center?->domain,
                'product_id' => (int) $item->product_id,
                'name' => $item->name,
                'sort_order' => (int) $item->sort_order,
            ])->values(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}
