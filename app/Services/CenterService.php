<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Center;
use App\Models\CenterUser;
use App\Models\Package;
use App\Models\Service;
use App\Models\CategoryService;
use App\Services\PageService;


use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CenterService
{
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


    public function getFilteredCenters($request)
    {
        $userLat = $request->input('lat');
        $userLng = $request->input('lng');
        $radius = (float) $request->input('radius', 50);
        $search = $request->filled('search') ? mb_strtolower(trim($request->search)) : null;
        $categoryId = $request->filled('category_id') ? (int) $request->category_id : null;
        $needsGeo = $userLat !== null && $userLng !== null;

        $centers = Center::where('status', 'approve')
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
            })
            ->with('globalCategories')
            ->get();

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
                    $radius
                );

                if ($matched === false) {
                    continue;
                }

                $matches[] = [
                    'center' => $center,
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

        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);
        $total = count($matches);
        $offset = ($page - 1) * $perPage;
        $pageMatches = array_slice($matches, $offset, $perPage);

        $filteredCenters = [];
        $originalDb = Config::get('database.connections.mysql.database');
        $userId = auth('center_api')->id();

        foreach ($pageMatches as $match) {
            $center = $match['center'];
            try {
                $this->hydrateCenterForList($center, $userId);
                $centerData = \App\Http\Resources\CenterResource::make($center)->resolve();
                $centerData['distance'] = $match['distance'] !== null ? round($match['distance'], 2) : null;
                $filteredCenters[] = $centerData;
            } catch (\Exception $e) {
                // Skip hydrate failures
            }
        }

        Config::set('database.connections.mysql.database', $originalDb);
        DB::purge('mysql');
        DB::reconnect('mysql');

        return new LengthAwarePaginator(
            $filteredCenters,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function getFilteredCentersDetail($request)
    {
        $search = $request->filled('search') ? mb_strtolower(trim($request->search)) : null;
        $categoryId = $request->filled('category_id') ? (int) $request->category_id : null;

        $centers = Center::where('status', 'approve')
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
            })
            ->with('globalCategories')
            ->get();

        $matches = [];

        foreach ($centers as $center) {
            if (!$center->database) {
                continue;
            }

            try {
                $matched = $this->centerMatchesFilters($center, $search, $categoryId, null, null, null);
                if ($matched === false) {
                    continue;
                }
                $matches[] = ['center' => $center, 'distance' => null];
            } catch (\Exception $e) {
                // Skip broken tenant DBs
            }
        }

        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);
        $total = count($matches);
        $offset = ($page - 1) * $perPage;
        $pageMatches = array_slice($matches, $offset, $perPage);

        $filteredCenters = [];
        $originalDb = Config::get('database.connections.mysql.database');
        $userId = auth('center_api')->id();

        foreach ($pageMatches as $match) {
            $center = $match['center'];
            try {
                $this->hydrateCenterForList($center, $userId);
                $filteredCenters[] = \App\Http\Resources\CenterResource::make($center)->resolve();
            } catch (\Exception $e) {
                // Skip hydrate failures
            }
        }

        Config::set('database.connections.mysql.database', $originalDb);
        DB::purge('mysql');
        DB::reconnect('mysql');

        return new LengthAwarePaginator(
            $filteredCenters,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Cheap tenant filter pass — no heavy eager loads.
     * Returns ['distance' => float|null] on match, false otherwise.
     */
    private function centerMatchesFilters(
        Center $center,
        ?string $search,
        ?int $categoryId,
        ?float $userLat,
        ?float $userLng,
        ?float $radius
    ) {
        $matchedByName = $search
            ? str_contains(mb_strtolower($center->name ?? ''), $search)
            : true;

        $needsTenant = ($search && !$matchedByName) || $categoryId || ($userLat !== null && $userLng !== null);

        $distance = null;

        if ($needsTenant) {
            $this->connectTenant($center->database);

            if ($categoryId && !$this->tenantHasCategory($categoryId)) {
                return false;
            }

            if ($search && !$matchedByName && !$this->tenantMatchesSearch($search)) {
                return false;
            }

            if ($userLat !== null && $userLng !== null) {
                $distance = $this->tenantMinDistance($userLat, $userLng);
                if ($distance !== null && $radius !== null && $distance > $radius) {
                    return false;
                }
            }
        } elseif ($search && !$matchedByName) {
            return false;
        }

        return ['distance' => $distance];
    }

    private function connectTenant(string $database): void
    {
        static $ready = false;

        $safeDb = str_replace('`', '``', $database);

        if (!$ready) {
            Config::set('database.connections.tenant.database', $database);
            DB::purge('tenant');
            DB::reconnect('tenant');
            $ready = true;
            return;
        }

        DB::connection('tenant')->getPdo()->exec("USE `{$safeDb}`");
    }

    private function tenantHasCategory(int $categoryId): bool
    {
        return DB::connection('tenant')
            ->table('categories_services')
            ->where('id', $categoryId)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function tenantMatchesSearch(string $search): bool
    {
        $like = '%' . $search . '%';

        if (DB::connection('tenant')
            ->table('category_service_translations')
            ->whereRaw('LOWER(name) LIKE ?', [$like])
            ->exists()) {
            return true;
        }

        return DB::connection('tenant')
            ->table('service_translations')
            ->whereRaw('LOWER(name) LIKE ?', [$like])
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

    private function hydrateCenterForList(Center $center, $userId = null): void
    {
        Config::set('database.connections.mysql.database', $center->database);
        DB::purge('mysql');
        DB::reconnect('mysql');

        $center->categories = CategoryService::with('services.workers.vacations')->get();
        $center->services = Service::with('workers.vacations')->where('is_top', true)->get();
        $center->packages = Package::all();
        $center->branches = Branch::all();
        $center->about_us = (new PageService())->aboutUs();

        if ($userId) {
            $center->user_packages = \App\Models\UserPackage::where('user_id', $userId)
                ->with(['package.translation'])
                ->get();
            $center->user_used_packages = \App\Models\UserUsedPackage::where('user_id', $userId)
                ->with(['service.translation'])
                ->get();
        }
    }


    public function add($request)
    {
        try {
            $request['database'] = $request['domain'];
            $request['expire_date'] = now()->addDays(15);
            $center = Center::create($request);
            if (isset($request['image'])) {
                $center->addMedia($request['image'])->toMediaCollection('Center');
            }
            if (isset($request['primary_image'])) {
                $images = is_array($request['primary_image']) ? $request['primary_image'] : [$request['primary_image']];
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
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function setupCenterDatabase(Center $center)
    {
        if ($center->is_setup) {
            return;
        }

        $dbName = $center->database;
        $originalDb = Config::get('database.connections.mysql.database');

        \Log::info('Attempting to create database', [
            'dbName' => $dbName,
            'dbHost' => env('DB_HOST', '127.0.0.1'),
            'dbUsername' => env('DB_USERNAME', 'luzori')
        ]);

        try {
            $new_db = DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Exception $dbError) {
            \Log::error('Database creation error', [
                'dbName' => $dbName,
                'error' => $dbError->getMessage(),
                'code' => $dbError->getCode()
            ]);
            $new_db = false;
        }

        if ($new_db) {
            try {
                // Configure tenant connection
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
                    '--path' => 'database/migrations/centers',
                    '--database' => 'tenant'
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
                        '--class' => $seeder,
                        '--database' => 'tenant',
                    ]);
                }

                $this->seedDefaultBranchTranslations($center);

                // Create Center User in the new database
                $userData = $center->only(['name', 'email', 'country_code', 'phone', 'currency']);
                $userData['created_at'] = now();
                $userData['updated_at'] = now();
                
                // Bypass model setter to avoid double hashing since Center->password is already hashed
                $userData['password'] = $center->password; 

                $centerUserId = DB::connection('tenant')->table('center_users')->insertGetId($userData);

                DB::connection('tenant')->table('model_has_roles')->insert([
                    'role_id' => 1, // Super Admin
                    'model_type' => 'App\Models\CenterUser',
                    'model_id' => $centerUserId,
                ]);

            } finally {
                // Restore the main connection
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
        $branchId = DB::connection('tenant')->table('branches')->orderBy('id')->value('id');

        if (!$branchId) {
            return;
        }

        $centerName = $center->name;
        $now = now();

        foreach (['en', 'ar'] as $locale) {
            DB::connection('tenant')->table('branch_translations')->insert([
                'branch_id' => $branchId,
                'name' => $centerName,
                'city' => $centerName,
                'address' => $centerName,
                'locale' => $locale,
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
            // Delete specific existing primary images by media ID
            if (!empty($request['delete_primary_images'])) {
                $mediaIds = array_filter(explode(',', $request['delete_primary_images']));
                foreach ($mediaIds as $mediaId) {
                    $media = $center->media()->where('id', $mediaId)->first();
                    if ($media) {
                        $media->delete();
                    }
                }
            }

            // Add new primary images (without clearing remaining existing ones)
            if (isset($request['primary_image'])) {
                $images = is_array($request['primary_image']) ? $request['primary_image'] : [$request['primary_image']];
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

            // Trigger setup if approved and not already setup
            if ($center->status == 'approve' && !$center->is_setup) {
                $this->setupCenterDatabase($center);
            }

            DB::commit();
            return $center;
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Center update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
