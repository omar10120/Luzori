<?php

namespace App\Services;

use App\Models\GlobalCategory;
use App\Models\Center;
use Illuminate\Support\Facades\DB;

class GlobalCategoryService
{
    private CRUDService $crudService;

    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    /**
     * Get all global categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories()
    {
        return $this->crudService->all('GlobalCategory', [], 0); // 0 = without trashed
    }

    /**
     * Sync categories for a specific center.
     *
     * @param Center|int $center
     * @param array $categoryIds
     * @return void
     */
    public function syncCenterCategories($center, array $categoryIds)
    {
        if (is_numeric($center)) {
            $center = Center::findOrFail($center);
        }

        $center->globalCategories()->sync($categoryIds);
    }

    /**
     * Get centers filtered by global category slug.
     *
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCentersByCategorySlug(string $slug)
    {
        return Center::whereHas('globalCategories', function ($query) use ($slug) {
            $query->where('slug', $slug);
        })->where('status', 'approve')->get();
    }
}
