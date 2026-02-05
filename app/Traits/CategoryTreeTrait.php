<?php

namespace App\Traits;

use App\Models\CategoryService;

trait CategoryTreeTrait
{
    /**
     * Get formatted categories for jstree
     * 
     * @param int $excludeId
     * @param bool $showBothLanguages
     * @return array
     */
    public function getFormattedCategories($excludeId = 0, $showBothLanguages = false)
    {
        $categories = CategoryService::with(['translation', 'children' => function($q) use ($excludeId) {
                if ($excludeId) {
                    $q->where('id', '!=', $excludeId);
                }
                $q->with('translation');
            }])
            ->whereNull('parent_id')
            ->when($excludeId, function($q) use ($excludeId) {
                return $q->where('id', '!=', $excludeId);
            })
            ->get();

        return $this->formatCategoriesForJsTree($categories, $excludeId, $showBothLanguages);
    }

    /**
     * Recursively format categories for jstree
     * 
     * @param mixed $cats
     * @param int $excludeId
     * @param bool $showBothLanguages
     * @return array
     */
    private function formatCategoriesForJsTree($cats, $excludeId = 0, $showBothLanguages = false)
    {
        $data = [];
        foreach ($cats as $cat) {
            if ($showBothLanguages) {
                $nameEn = $cat->translate('en')->name ?? '';
                $nameAr = $cat->translate('ar')->name ?? '';
                $name = trim($nameAr . ' / ' . $nameEn, ' / ');
            } else {
                $name = $cat->translate(app()->getLocale())->name ?? $cat->name;
            }

            $children = [];
            if (isset($cat->children)) {
                $children = $this->formatCategoriesForJsTree($cat->children, $excludeId, $showBothLanguages);
            }

            $data[] = [
                'id' => (string)$cat->id,
                'text' => $name,
                'children' => $children
            ];
        }
        return $data;
    }
}
