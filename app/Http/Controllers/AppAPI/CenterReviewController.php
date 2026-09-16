<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterReview;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CenterReviewController extends Controller
{
    /**
     * My reviews list.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $reviews = CenterReview::with(['center.globalCategories', 'user'])
            ->where('user_id', $user->id)
            ->whereHas('center', function ($q) {
                $q->where('status', 'approve')
                    ->where(function ($q) {
                        $q->whereNull('expire_date')->orWhere('expire_date', '>', now());
                    });
            })
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        $data = collect($reviews->items())->map(function (CenterReview $review) {
            return $this->formatReview($review, true);
        })->values();

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'reviews' => $data,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Reviews for a center (public for authenticated app users).
     */
    public function byCenter(Request $request, $centerId)
    {
        $center = $this->findApprovedCenter((int) $centerId);
        if (!$center) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        $reviews = CenterReview::with('user')
            ->where('center_id', $center->id)
            ->latest()
            ->paginate((int) $request->input('per_page', 20));

        $stats = $this->centerReviewStats($center->id);

        $data = collect($reviews->items())->map(function (CenterReview $review) {
            return $this->formatReview($review, false);
        })->values();

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'center_id' => $center->id,
            'avg_rating' => $stats['avg_rating'],
            'reviews_count' => $stats['reviews_count'],
            'reviews' => $data,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Create or update my review for a center.
     */
    public function store(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $center = $this->findApprovedCenter((int) $request->center_id);
        if (!$center) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        $review = CenterReview::updateOrCreate(
            [
                'user_id' => $user->id,
                'center_id' => $center->id,
            ],
            [
                'rating' => (int) $request->rating,
                'comment' => $request->comment,
            ]
        );

        $review->load(['center.globalCategories', 'user']);
        $wasRecentlyCreated = $review->wasRecentlyCreated;

        return MyHelper::responseJSON(
            $wasRecentlyCreated ? __('api.reviewAdded') : __('api.reviewUpdated'),
            $wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK,
            $this->formatReview($review, true)
        );
    }

    /**
     * Update my review for a center.
     */
    public function update(Request $request, $centerId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = CenterReview::where('user_id', $request->user()->id)
            ->where('center_id', (int) $centerId)
            ->first();

        if (!$review) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        $review->update([
            'rating' => (int) $request->rating,
            'comment' => $request->comment,
        ]);

        $review->load(['center.globalCategories', 'user']);

        return MyHelper::responseJSON(__('api.reviewUpdated'), Response::HTTP_OK, $this->formatReview($review, true));
    }

    public function destroy(Request $request, $centerId)
    {
        $deleted = CenterReview::where('user_id', $request->user()->id)
            ->where('center_id', (int) $centerId)
            ->delete();

        if (!$deleted) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        return MyHelper::responseJSON(__('api.reviewRemoved'), Response::HTTP_OK);
    }

    public function mine(Request $request, $centerId)
    {
        $review = CenterReview::with(['center.globalCategories', 'user'])
            ->where('user_id', $request->user()->id)
            ->where('center_id', (int) $centerId)
            ->first();

        if (!$review) {
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
                'center_id' => (int) $centerId,
                'has_review' => false,
                'review' => null,
            ]);
        }

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'center_id' => (int) $centerId,
            'has_review' => true,
            'review' => $this->formatReview($review, true),
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

    private function centerReviewStats(int $centerId): array
    {
        $row = CenterReview::where('center_id', $centerId)
            ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as avg_rating')
            ->first();

        return [
            'reviews_count' => (int) ($row->reviews_count ?? 0),
            'avg_rating' => $row && $row->reviews_count
                ? round((float) $row->avg_rating, 1)
                : null,
        ];
    }

    private function formatReview(CenterReview $review, bool $withCenter = false): array
    {
        $user = $review->user;

        $data = [
            'id' => $review->id,
            'center_id' => $review->center_id,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'image' => method_exists($user, 'getFirstMediaUrl')
                    ? ($user->getFirstMediaUrl('PrimaryImage') ?: null)
                    : null,
            ] : null,
            'created_at' => $review->created_at,
            'updated_at' => $review->updated_at,
        ];

        if ($withCenter && $review->relationLoaded('center') && $review->center) {
            $center = $review->center;
            $data['center'] = [
                'id' => $center->id,
                'name' => $center->name,
                'domain' => $center->domain,
                'status' => $center->status,
                'rate' => $center->rate,
                'logo' => $center->getFirstMediaUrl('Center') ?: asset('assets/img/avatars/1.png'),
            ];
        }

        return $data;
    }
}
