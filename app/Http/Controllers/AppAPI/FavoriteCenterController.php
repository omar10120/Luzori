<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\FavoriteCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FavoriteCenterController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $favorites = FavoriteCenter::with(['center.globalCategories'])
            ->where('user_id', $user->id)
            ->whereHas('center', function ($q) {
                $q->where('status', 'approve')
                    ->where(function ($q) {
                        $q->whereNull('expire_date')->orWhere('expire_date', '>', now());
                    });
            })
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        $data = collect($favorites->items())->map(function (FavoriteCenter $favorite) {
            return $this->formatCenter($favorite->center, $favorite->created_at);
        })->filter()->values();

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'favorites' => $data,
            'pagination' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
        ]);

        $center = $this->findApprovedCenter((int) $request->center_id);
        if (!$center) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        $favorite = FavoriteCenter::firstOrCreate([
            'user_id' => $user->id,
            'center_id' => $center->id,
        ]);

        $wasRecentlyCreated = $favorite->wasRecentlyCreated;

        return MyHelper::responseJSON(
            $wasRecentlyCreated ? __('api.favoriteAdded') : __('api.favoriteAlreadyExists'),
            $wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK,
            $this->formatCenter($center, $favorite->created_at)
        );
    }

    public function destroy(Request $request, $centerId)
    {
        $deleted = FavoriteCenter::where('user_id', $request->user()->id)
            ->where('center_id', (int) $centerId)
            ->delete();

        if (!$deleted) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        return MyHelper::responseJSON(__('api.favoriteRemoved'), Response::HTTP_OK);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
        ]);

        $center = $this->findApprovedCenter((int) $request->center_id);
        if (!$center) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();
        $existing = FavoriteCenter::where('user_id', $user->id)
            ->where('center_id', $center->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return MyHelper::responseJSON(__('api.favoriteRemoved'), Response::HTTP_OK, [
                'is_favorite' => false,
                'center_id' => $center->id,
            ]);
        }

        $favorite = FavoriteCenter::create([
            'user_id' => $user->id,
            'center_id' => $center->id,
        ]);

        return MyHelper::responseJSON(__('api.favoriteAdded'), Response::HTTP_CREATED, [
            'is_favorite' => true,
            'center' => $this->formatCenter($center, $favorite->created_at),
        ]);
    }

    public function check(Request $request, $centerId)
    {
        $isFavorite = FavoriteCenter::where('user_id', $request->user()->id)
            ->where('center_id', (int) $centerId)
            ->exists();

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'center_id' => (int) $centerId,
            'is_favorite' => $isFavorite,
        ]);
    }

    private function findApprovedCenter(int $centerId): ?Center
    {
        return Center::where('id', $centerId)
            ->where('status', 'approve')
            ->where(function ($q) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>', now());
            })
            ->with('globalCategories')
            ->first();
    }

    private function formatCenter(?Center $center, $favoritedAt = null): ?array
    {
        if (!$center) {
            return null;
        }

        return [
            'id' => $center->id,
            'name' => $center->name,
            'domain' => $center->domain,
            'status' => $center->status,
            'rate' => $center->rate,
            'logo' => $center->getFirstMediaUrl('Center') ?: asset('assets/img/avatars/1.png'),
            'primary_images' => $center->getMedia('PrimaryImage')->map(fn ($m) => $m->getUrl())->toArray(),
            'global_categories' => $center->relationLoaded('globalCategories')
                ? $center->globalCategories->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug ?? null,
                ])->values()
                : [],
            'is_favorite' => true,
            'favorited_at' => $favoritedAt,
        ];
    }
}
