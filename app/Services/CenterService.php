<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Center;
use App\Models\CenterUser;
use App\Models\Package;
use App\Models\Service;
use App\Models\CategoryService;
use App\Models\AppUser;
use App\Models\FavoriteCenter;
use App\Models\CenterReview;
use App\Services\PageService;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CenterService
{
    // =========================================================================
    // BASIC
    // =========================================================================
    public function all()
    {
        return Center::withTrashed()->get();
    }

    public function find($id)
    {
        return Center::withTrashed()->find($id);
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // =========================================================================
    // INCLUDES
    // =========================================================================
    /**
     * Parse the ?include=... query param into an allowlisted array.
     *
     * Examples:
     *   ?include=branches,packages
     *   ?include[]=branches&include[]=services
     *   (no include)  -> defaults to ['global_categories']
     */
    private function parseIncludes($request): array
    {
        $allowed = [
            'global_categories',
            'branches',
            'categories',
            'services',
            'packages',
            'about_us',
            'user_packages',
            'user_used_packages',
            'workers',      // only meaningful with categories/services
            'vacations',    // only meaningful with workers
            'reviews',
        ];

        if (!$request->has('include')) {
            return ['global_categories'];
        }

        $raw   = $request->input('include');
        $items = is_array($raw) ? $raw : explode(',', (string) $raw);

        $items = array_map(fn($v) => strtolower(trim((string) $v)), $items);
        $items = array_values(array_intersect($items, $allowed));

        if (empty($items)) {
            return ['global_categories'];
        }

        // workers/vacations only make sense with categories or services
        if (in_array('workers', $items, true)
            && !array_intersect($items, ['categories', 'services'])) {
            $items = array_values(array_diff($items, ['workers', 'vacations']));
        }

        if (!in_array('global_categories', $items, true)) {
            // global_categories is cheap (main DB) — keep it always on unless
            // the client explicitly asked for a very narrow include set.
            // Remove this block if you want it strictly opt-in.
        }

        return $items;
    }

    // =========================================================================
    // LIST (with geo + search + filters)
    // =========================================================================
    public function getFilteredCenters($request)
    {
        $userLat    = $request->input('lat');
        $userLng    = $request->input('lng');
        $radius     = (float) $request->input('radius', 50);
        $search     = $request->filled('search') ? mb_strtolower(trim($request->search)) : null;
        $categoryId = $request->filled('category_id') ? (int) $request->category_id : null;
        $needsGeo   = $userLat !== null && $userLng !== null;

        $includes = $this->parseIncludes($request);

        $centersQuery = Center::where('status', 'approve')
            ->where(function ($q) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>', now());
            })
            ->when($request->filled('rate'), function ($q) use ($request) {
                $q->where('rate', $request->rate);
            })
            ->when($request->filled('global_category_id'), function ($q) use ($request) {
                $q->whereHas('globalCategories', function ($q) use ($request) {
                    $q->where('global_categories.id', (int) $request->global_category_id);
                });
            });

        $this->applyIsFavoriteFilter($centersQuery, $request);

        if (in_array('global_categories', $includes, true)) {
            $centersQuery->with('globalCategories');
        }

        $centers = $centersQuery->get();

        // One batch tenant search across all tenant DBs (instead of N queries)
        $tenantSearchHits = $search
            ? $this->batchTenantSearch($centers, $search)
            : [];

        $matches = [];

        foreach ($centers as $center) {
            if (!$center->database) {
                continue;
            }

            try {
                $matched = $this->centerMatchesFilters(
                    $center,
                    $search,
                    $categoryId,
                    $needsGeo ? (float) $userLat : null,
                    $needsGeo ? (float) $userLng : null,
                    $radius,
                    $tenantSearchHits
                );

                if ($matched === false) {
                    continue;
                }

                $matches[] = [
                    'center'   => $center,
                    'distance' => $matched['distance'],
                ];
            } catch (\Exception $e) {
                // Skip broken tenant DBs
            }
        }

        if ($needsGeo) {
            usort($matches, function ($a, $b) {
                if ($a['distance'] === null && $b['distance'] === null) {
                    return 0;
                }
                if ($a['distance'] === null) {
                    return 1;
                }
                if ($b['distance'] === null) {
                    return -1;
                }
                return $a['distance'] <=> $b['distance'];
            });
        }

        $perPage     = (int) $request->input('per_page', 15);
        $page        = (int) $request->input('page', 1);
        $total       = count($matches);
        $offset      = ($page - 1) * $perPage;
        $pageMatches = array_slice($matches, $offset, $perPage);

        $filteredCenters = [];
        $originalDb      = Config::get('database.connections.mysql.database');
        $userId          = auth('center_api')->id();
        $pageCenterIds   = collect($pageMatches)->pluck('center.id')->map(fn ($id) => (int) $id)->all();
        $favoriteIds     = $this->favoriteCenterIdsForRequest($request, $pageCenterIds);
        $reviewStats     = $this->reviewStatsForCenters($pageCenterIds);
        $myReviews       = $this->myReviewsForRequest($request, $pageCenterIds);

        foreach ($pageMatches as $match) {
            $center = $match['center'];
            try {
                $this->hydrateCenterForList($center, $userId, $includes);

                // Serialize while still on tenant DB (nested translations lazy-load here)
                $centerData = json_decode(
                    \App\Http\Resources\CenterResource::make($center)->toJson(),
                    true
                );

                $centerData['distance'] = $match['distance'] !== null
                    ? round($match['distance'], 2)
                    : null;
                $centerData['is_favorite'] = in_array((int) $center->id, $favoriteIds, true);
                $this->attachReviewFields($centerData, (int) $center->id, $reviewStats, $myReviews);

                $filteredCenters[] = $centerData;
            } catch (\Exception $e) {
                Log::warning('Center list hydrate failed', [
                    'center_id' => $center->id ?? null,
                    'database'  => $center->database ?? null,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        $this->restoreMainDatabase($originalDb);

        return new LengthAwarePaginator(
            $filteredCenters,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    // =========================================================================
    // DETAIL (no geo)
    // =========================================================================
    public function getFilteredCentersDetail($request)
    {
        $search     = $request->filled('search') ? mb_strtolower(trim($request->search)) : null;
        $categoryId = $request->filled('category_id') ? (int) $request->category_id : null;

        $includes = $this->parseIncludes($request);

        $centersQuery = Center::where('status', 'approve')
            ->where(function ($q) {
                $q->whereNull('expire_date')->orWhere('expire_date', '>', now());
            })
            ->when($request->filled('rate'), function ($q) use ($request) {
                $q->where('rate', $request->rate);
            })
            ->when($request->filled('global_category_id'), function ($q) use ($request) {
                $q->whereHas('globalCategories', function ($q) use ($request) {
                    $q->where('global_categories.id', (int) $request->global_category_id);
                });
            });

        $this->applyIsFavoriteFilter($centersQuery, $request);

        if (in_array('global_categories', $includes, true)) {
            $centersQuery->with('globalCategories');
        }

        $centers = $centersQuery->get();

        // ---- Batch tenant search (same as list) ----
        $tenantSearchHits = $search
            ? $this->batchTenantSearch($centers, $search)
            : [];

        $matches = [];

        foreach ($centers as $center) {
            if (!$center->database) {
                continue;
            }

            try {
                $matched = $this->centerMatchesFilters(
                    $center,
                    $search,
                    $categoryId,
                    null,
                    null,
                    null,
                    $tenantSearchHits
                );

                if ($matched === false) {
                    continue;
                }

                $matches[] = ['center' => $center, 'distance' => null];
            } catch (\Exception $e) {
                // Skip broken tenant DBs
            }
        }

        $perPage     = (int) $request->input('per_page', 15);
        $page        = (int) $request->input('page', 1);
        $total       = count($matches);
        $offset      = ($page - 1) * $perPage;
        $pageMatches = array_slice($matches, $offset, $perPage);

        $filteredCenters = [];
        $originalDb      = Config::get('database.connections.mysql.database');
        $userId          = auth('center_api')->id();
        $pageCenterIds   = collect($pageMatches)->pluck('center.id')->map(fn ($id) => (int) $id)->all();
        $favoriteIds     = $this->favoriteCenterIdsForRequest($request, $pageCenterIds);
        $reviewStats     = $this->reviewStatsForCenters($pageCenterIds);
        $myReviews       = $this->myReviewsForRequest($request, $pageCenterIds);

        foreach ($pageMatches as $match) {
            $center = $match['center'];
            try {
                $this->hydrateCenterForList($center, $userId, $includes);
                $centerData = json_decode(
                    \App\Http\Resources\CenterResource::make($center)->toJson(),
                    true
                );
                $centerData['is_favorite'] = in_array((int) $center->id, $favoriteIds, true);
                $this->attachReviewFields($centerData, (int) $center->id, $reviewStats, $myReviews);
                $filteredCenters[] = $centerData;
            } catch (\Exception $e) {
                Log::warning('Center detail hydrate failed', [
                    'center_id' => $center->id ?? null,
                    'database'  => $center->database ?? null,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        $this->restoreMainDatabase($originalDb);

        return new LengthAwarePaginator(
            $filteredCenters,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    // =========================================================================
    // FILTER MATCHER
    // =========================================================================
    /**
     * Cheap tenant filter pass — no heavy eager loads.
     * Returns ['distance' => float|null] on match, false otherwise.
     *
     * $tenantSearchHits is a precomputed array of tenant DB names that matched
     * the search term (from batchTenantSearch). Passed in to avoid per-center
     * tenant queries.
     */
    private function centerMatchesFilters(
        Center $center,
        ?string $search,
        ?int $categoryId,
        ?float $userLat,
        ?float $userLng,
        ?float $radius,
        array $tenantSearchHits = []
    ) {
        $matchedByName = $search
            ? str_contains(mb_strtolower($center->name ?? ''), $search)
            : true;

        $matchedByTenantSearch = $search
            ? in_array($center->database, $tenantSearchHits, true)
            : false;

        $needsGeo = $userLat !== null && $userLng !== null;

        // Fast exit: search-only request, no name match, no tenant match
        if (!$categoryId && !$needsGeo && $search && !$matchedByName && !$matchedByTenantSearch) {
            return false;
        }

        $distance = null;

        // Only connect to tenant when there's an actual reason to
        if ($categoryId || $needsGeo) {
            $this->connectTenant($center->database);

            if ($categoryId && !$this->tenantHasCategory($categoryId)) {
                return false;
            }

            if ($needsGeo) {
                $distance = $this->tenantMinDistance($userLat, $userLng);
                if ($distance !== null && $radius !== null && $distance > $radius) {
                    return false;
                }
            }
        }

        return ['distance' => $distance];
    }

    // =========================================================================
    // TENANT CONNECTION
    // =========================================================================
    private function connectTenant(string $database): void
    {
        $safeDb = str_replace('`', '``', $database);

        Config::set('database.connections.tenant.database', $database);

        try {
            // Reuse existing PDO connection and just switch DB
            DB::connection('tenant')->getPdo()->exec("USE `{$safeDb}`");
        } catch (\Exception $e) {
            // Connection was never established — build it fresh
            DB::purge('tenant');
            DB::reconnect('tenant');
            DB::connection('tenant')->getPdo()->exec("USE `{$safeDb}`");
        }
    }

    private function restoreMainDatabase(string $originalDb): void
    {
        $safeDb = str_replace('`', '``', $originalDb);
        try {
            DB::connection('mysql')->getPdo()->exec("USE `{$safeDb}`");
        } catch (\Exception $e) {
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
        Config::set('database.connections.mysql.database', $originalDb);
    }

    /**
     * Favorite center IDs for the authenticated AppUser (optional Bearer token).
     *
     * @param  array<int>  $centerIds
     * @return array<int>
     */
    private function favoriteCenterIdsForRequest($request, array $centerIds): array
    {
        $centerIds = array_values(array_filter(array_map('intval', $centerIds)));
        if ($centerIds === []) {
            return [];
        }

        $user = $request->user();
        if (!$user instanceof AppUser) {
            return [];
        }

        return FavoriteCenter::where('user_id', $user->id)
            ->whereIn('center_id', $centerIds)
            ->pluck('center_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array<int>  $centerIds
     * @return array<int, array{avg_rating: ?float, reviews_count: int}>
     */
    private function reviewStatsForCenters(array $centerIds): array
    {
        $centerIds = array_values(array_filter(array_map('intval', $centerIds)));
        if ($centerIds === []) {
            return [];
        }

        $rows = CenterReview::whereIn('center_id', $centerIds)
            ->selectRaw('center_id, COUNT(*) as reviews_count, AVG(rating) as avg_rating')
            ->groupBy('center_id')
            ->get();

        $stats = [];
        foreach ($rows as $row) {
            $stats[(int) $row->center_id] = [
                'avg_rating' => round((float) $row->avg_rating, 1),
                'reviews_count' => (int) $row->reviews_count,
            ];
        }

        return $stats;
    }

    /**
     * @param  array<int>  $centerIds
     * @return array<int, array{id:int,rating:int,comment:?string}>
     */
    private function myReviewsForRequest($request, array $centerIds): array
    {
        $centerIds = array_values(array_filter(array_map('intval', $centerIds)));
        if ($centerIds === []) {
            return [];
        }

        $user = $request->user();
        if (!$user instanceof AppUser) {
            return [];
        }

        return CenterReview::where('user_id', $user->id)
            ->whereIn('center_id', $centerIds)
            ->get(['id', 'center_id', 'rating', 'comment'])
            ->keyBy(fn ($r) => (int) $r->center_id)
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'rating' => (int) $r->rating,
                'comment' => $r->comment,
            ])
            ->all();
    }

    private function attachReviewFields(array &$centerData, int $centerId, array $reviewStats, array $myReviews): void
    {
        $stats = $reviewStats[$centerId] ?? null;
        $centerData['avg_rating'] = $stats['avg_rating'] ?? null;
        $centerData['reviews_count'] = $stats['reviews_count'] ?? 0;
        $centerData['has_review'] = isset($myReviews[$centerId]);
        $centerData['my_review'] = $myReviews[$centerId] ?? null;
    }

    /**
     * Filter by isFavorite / is_favorite query param.
     * true  → only favorited centers (requires AppUser token)
     * false → only non-favorited centers
     */
    private function applyIsFavoriteFilter($centersQuery, $request): void
    {
        $raw = $request->input('isFavorite', $request->input('is_favorite'));
        if ($raw === null || $raw === '') {
            return;
        }

        $wantFavorite = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($wantFavorite === null) {
            // Accept "1"/"0" already handled by FILTER_VALIDATE_BOOLEAN; reject invalid
            return;
        }

        $user = $request->user();
        if (!$user instanceof AppUser) {
            if ($wantFavorite) {
                $centersQuery->whereRaw('1 = 0');
            }
            return;
        }

        $favIds = FavoriteCenter::where('user_id', $user->id)->pluck('center_id');

        if ($wantFavorite) {
            $centersQuery->whereIn('id', $favIds);
        } else {
            $centersQuery->whereNotIn('id', $favIds);
        }
    }

    // =========================================================================
    // TENANT QUERIES
    // =========================================================================
    private function tenantHasCategory(int $categoryId): bool
    {
        return DB::connection('tenant')
            ->table('categories_services')
            ->where('id', $categoryId)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function tenantMinDistance(float $userLat, float $userLng): ?float
    {
        $branches = DB::connection('tenant')
            ->table('branches')
            ->whereNull('deleted_at')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['latitude', 'longitude']);

        if ($branches->isEmpty()) {
            return null;
        }

        $min = null;
        foreach ($branches as $branch) {
            $dist = $this->haversineDistance(
                $userLat,
                $userLng,
                (float) $branch->latitude,
                (float) $branch->longitude
            );
            if ($min === null || $dist < $min) {
                $min = $dist;
            }
        }

        return $min;
    }

    /**
     * Search all tenant DBs in a single UNION query.
     * Returns an array of database names that matched.
     *
     * Pre-filter via information_schema so a half-setup tenant can't break the
     * whole UNION.
     */
    private function batchTenantSearch($centers, string $search): array
    {
        $dbNames = collect($centers)
            ->pluck('database')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($dbNames)) {
            return [];
        }

        // Filter to DBs that actually exist and have the tables we need.
        try {
            $existing = DB::connection('mysql')
                ->table('information_schema.tables')
                ->whereIn('table_schema', $dbNames)
                ->whereIn('table_name', ['category_service_translations', 'service_translations'])
                ->pluck('table_schema')
                ->unique()
                ->all();
        } catch (\Exception $e) {
            Log::warning('batchTenantSearch prefilter failed', ['error' => $e->getMessage()]);
            $existing = $dbNames; // fall back to trying everything
        }

        $existing = array_flip($existing);

        $like     = '%' . $search . '%';
        $unions   = [];
        $bindings = [];

        foreach ($dbNames as $dbName) {
            if (!isset($existing[$dbName])) {
                continue;
            }
            $db = str_replace('`', '``', $dbName);

            $unions[] = "SELECT '{$db}' AS db_name
                         FROM `{$db}`.category_service_translations
                         WHERE LOWER(name) LIKE ? LIMIT 1";
            $bindings[] = $like;

            $unions[] = "SELECT '{$db}' AS db_name
                         FROM `{$db}`.service_translations
                         WHERE LOWER(name) LIKE ? LIMIT 1";
            $bindings[] = $like;
        }

        if (empty($unions)) {
            return [];
        }

        try {
            $rows = DB::connection('mysql')
                ->select(implode(' UNION ALL ', $unions), $bindings);
        } catch (\Exception $e) {
            Log::warning('batchTenantSearch failed', [
                'error'        => $e->getMessage(),
                'tenant_count' => count($unions),
            ]);
            return [];
        }

        return array_values(array_unique(array_column($rows, 'db_name')));
    }

    // =========================================================================
    // HYDRATION
    // =========================================================================
    /**
     * Load only the relations requested via ?include=...
     * Always keeps global_categories off the tenant (it comes from the main DB
     * and is loaded on the Center query itself).
     */
    private function hydrateCenterForList(
        Center $center,
        $userId = null,
        array $includes = ['global_categories']
    ): void {
        // Switch MySQL connection to the tenant DB without purge/reconnect.
        $safeDb = str_replace('`', '``', $center->database);
        DB::connection('mysql')->getPdo()->exec("USE `{$safeDb}`");
        Config::set('database.connections.mysql.database', $center->database);

        $withWorkers  = in_array('workers',   $includes, true);
        $withVacation = in_array('vacations', $includes, true);

        if (in_array('categories', $includes, true)) {
            $cats = ['translations', 'services.translations'];
            if ($withWorkers) {
                $cats[] = $withVacation ? 'services.workers.vacations' : 'services.workers';
            }
            $center->categories = CategoryService::with($cats)->get();
        }

        if (in_array('services', $includes, true)) {
            $svc = ['translations'];
            if ($withWorkers) {
                $svc[] = $withVacation ? 'workers.vacations' : 'workers';
            }
            $center->services = Service::with($svc)->where('is_top', true)->get();
        }

        if (in_array('packages', $includes, true)) {
            $center->packages = Package::with('translations')->get();
        }

        if (in_array('branches', $includes, true)) {
            $center->branches = Branch::with('translations')->get();
        }

        if (in_array('about_us', $includes, true)) {
            $center->about_us = (new PageService())->aboutUs();
        }

        if ($userId && in_array('user_packages', $includes, true)) {
            $center->user_packages = \App\Models\UserPackage::where('user_id', $userId)
                ->with(['package.translations'])
                ->get();
        }

        if ($userId && in_array('user_used_packages', $includes, true)) {
            $center->user_used_packages = \App\Models\UserUsedPackage::where('user_id', $userId)
                ->with(['service.translations'])
                ->get();
        }

        // Reviews live on central DB — safe while mysql is on tenant
        if (in_array('reviews', $includes, true)) {
            $center->setRelation(
                'reviews',
                CenterReview::with('user')
                    ->where('center_id', $center->id)
                    ->latest()
                    ->get()
            );
        }
    }

    // =========================================================================
    // CRUD
    // =========================================================================
    public function add($request)
    {
        try {
            $request['database']    = $request['domain'];
            $request['expire_date'] = now()->addDays(15);
            $center = Center::create($request);

            if (isset($request['image'])) {
                $center->addMedia($request['image'])->toMediaCollection('Center');
            }

            if (isset($request['primary_image'])) {
                $images = is_array($request['primary_image'])
                    ? $request['primary_image']
                    : [$request['primary_image']];
                foreach ($images as $img) {
                    $center->addMedia($img)->toMediaCollection('PrimaryImage');
                }
            }

            $center->assignRole($request['role']);

            if ($center->status == 'approve') {
                $this->setupCenterDatabase($center);
            }

            return $center;
        } catch (Exception $e) {
            \Log::error('Center creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function setupCenterDatabase(Center $center)
    {
        if ($center->is_setup) {
            return;
        }

        $dbName     = $center->database;
        $originalDb = Config::get('database.connections.mysql.database');

        \Log::info('Attempting to create database', [
            'dbName'     => $dbName,
            'dbHost'     => env('DB_HOST', '127.0.0.1'),
            'dbUsername' => env('DB_USERNAME', 'luzori'),
        ]);

        try {
            $new_db = DB::statement(
                "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
            );
        } catch (\Exception $dbError) {
            \Log::error('Database creation error', [
                'dbName' => $dbName,
                'error'  => $dbError->getMessage(),
                'code'   => $dbError->getCode(),
            ]);
            $new_db = false;
        }

        if ($new_db) {
            try {
                Config::set('database.connections.tenant.database', $dbName);
                Config::set('database.connections.tenant.host', env('DB_HOST', '127.0.0.1'));
                Config::set('database.connections.tenant.port', env('DB_PORT', '3306'));
                Config::set('database.connections.tenant.username', env('DB_USERNAME', 'luzori'));
                Config::set('database.connections.tenant.password', env('DB_PASSWORD', 'LuzoriStrongPass'));
                Config::set('database.connections.mysql.database', $dbName);

                DB::purge('tenant');
                DB::purge('mysql');
                DB::reconnect('tenant');
                DB::reconnect('mysql');

                Artisan::call('migrate', [
                    '--path'     => 'database/migrations/centers',
                    '--database' => 'tenant',
                ]);

                $seeders = [
                    'WeekDaySeeder',
                    'CenterUserPermissionSeeder',
                    'CenterUserRoleSeeder',
                    'LanguageSeeder',
                    'InfoSeeder',
                    'PageSeeder',
                    'SettingSeeder',
                    'PaymentMethodSeeder',
                    'WorkerSeeder',
                    'BranchSeeder',
                ];

                foreach ($seeders as $seeder) {
                    Artisan::call('db:seed', [
                        '--class'    => $seeder,
                        '--database' => 'tenant',
                    ]);
                }

                $this->seedDefaultBranchTranslations($center);

                $userData = $center->only(['name', 'email', 'country_code', 'phone', 'currency']);
                $userData['created_at'] = now();
                $userData['updated_at'] = now();
                $userData['password']   = $center->password; // already hashed

                $centerUserId = DB::connection('tenant')
                    ->table('center_users')
                    ->insertGetId($userData);

                DB::connection('tenant')->table('model_has_roles')->insert([
                    'role_id'    => 1,
                    'model_type' => 'App\Models\CenterUser',
                    'model_id'   => $centerUserId,
                ]);
            } finally {
                Config::set('database.connections.mysql.database', $originalDb);
                DB::purge('mysql');
                DB::reconnect('mysql');
            }

            $center->update(['is_setup' => true]);
            \Log::info("Center setup completed successfully: " . $dbName);
        }
    }

    private function seedDefaultBranchTranslations(Center $center): void
    {
        $branchId = DB::connection('tenant')
            ->table('branches')
            ->orderBy('id')
            ->value('id');

        if (!$branchId) {
            return;
        }

        $centerName = $center->name;
        $now = now();

        foreach (['en', 'ar'] as $locale) {
            DB::connection('tenant')->table('branch_translations')->insert([
                'branch_id'  => $branchId,
                'name'       => $centerName,
                'city'       => $centerName,
                'address'    => $centerName,
                'locale'     => $locale,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function edit($request)
    {
        DB::beginTransaction();
        try {
            $center = Center::withTrashed()->find($request['id']);

            if (isset($request['image'])) {
                $center->clearMediaCollection('Center');
                $center->addMedia($request['image'])->toMediaCollection('Center');
            }

            if (!empty($request['delete_primary_images'])) {
                $mediaIds = array_filter(explode(',', $request['delete_primary_images']));
                foreach ($mediaIds as $mediaId) {
                    $media = $center->media()->where('id', $mediaId)->first();
                    if ($media) {
                        $media->delete();
                    }
                }
            }

            if (isset($request['primary_image'])) {
                $images = is_array($request['primary_image'])
                    ? $request['primary_image']
                    : [$request['primary_image']];
                foreach ($images as $img) {
                    $center->addMedia($img)->toMediaCollection('PrimaryImage');
                }
            }

            if (isset($request['password']) && empty($request['password'])) {
                unset($request['password']);
            }

            $center->update($request);

            if (isset($request['role'])) {
                $center->roles()->detach();
                $center->assignRole($request['role']);
            }

            if ($center->status == 'approve' && !$center->is_setup) {
                $this->setupCenterDatabase($center);
            }

            DB::commit();
            return $center;
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Center update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function delete($id)
    {
        $center = Center::withTrashed()->find($id);
        $center->tokens()->delete();
        $center->fcmTokens()->delete();
        $center->delete();
        return $center;
    }
}