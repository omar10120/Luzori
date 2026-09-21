<?php

namespace App\Http\Controllers\CenterAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'placement_type' => ['nullable', Rule::in([
                'home', 'mobile', 'web', 'sidebar', 'checkout', 'category_page', 'service_page',
            ])],
            'center_id' => ['nullable', 'integer', 'exists:central.centers,id'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 100);

        $paginator = Banner::query()
            ->with(['center', 'globalCategory'])
            ->where('is_active', true)
            ->when(isset($validated['placement_type']), fn ($query) => $query->where(
                'placement_type', $validated['placement_type']
            ))
            ->when(isset($validated['center_id']), function ($query) use ($validated) {
                $query->where(function ($targetQuery) use ($validated) {
                    $targetQuery->where('center_id', $validated['center_id'])
                        ->orWhere('link_type', 'external');
                });
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $data = [
            'data' => BannerResource::collection($paginator->items()),
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $data);
    }
}
