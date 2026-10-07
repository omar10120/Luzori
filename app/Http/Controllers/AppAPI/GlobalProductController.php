<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
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
            'products' => $products->items(),
            'pagination' => $this->pagination($products),
        ]);
    }

    public function centerProducts(Request $request, int $centerId, GlobalProductService $globalProductService)
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);
        $center = Center::findOrFail($centerId);
        $products = $globalProductService->publicProductsForCenter(
            $center,
            (int) ($validated['per_page'] ?? 20)
        );

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'center_id' => $center->id,
            'products' => $products->items(),
            'pagination' => $this->pagination($products),
        ]);
    }

    public function show(int $centerId, int $productId, GlobalProductService $globalProductService)
    {
        $center = Center::findOrFail($centerId);
        $product = $globalProductService->publicProductForCenter($center, $productId);

        if (!$product) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $product);
    }

    private function pagination($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
