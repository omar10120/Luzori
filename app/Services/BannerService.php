<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\Center;
use App\Models\GlobalCategory;
use App\Models\Service;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BannerService
{
    public function options(): array
    {
        $centers = Center::query()
            ->where('status', 'approve')
            ->whereNotNull('database')
            ->orderBy('name')
            ->get(['id', 'name', 'database']);

        $services = [];
        $originalDatabase = config('database.connections.mysql.database');

        foreach ($centers as $center) {
            try {
                Config::set('database.connections.mysql.database', $center->database);
                DB::purge('mysql');
                DB::reconnect('mysql');

                $services[$center->id] = Service::query()
                    ->with('translation')
                    ->orderBy('id')
                    ->get()
                    ->map(fn ($service) => [
                        'id' => $service->id,
                        'name' => $service->name,
                    ])
                    ->values()
                    ->all();
            } catch (\Throwable) {
                $services[$center->id] = [];
            }
        }

        Config::set('database.connections.mysql.database', $originalDatabase);
        DB::purge('mysql');
        DB::reconnect('mysql');

        return [
            'centers' => $centers->map(fn ($center) => [
                'id' => $center->id,
                'name' => $center->name,
            ])->values(),
            'categories' => GlobalCategory::query()->orderBy('name')->get(['id', 'name', 'nameAr']),
            'services' => $services,
        ];
    }

    public function store(array $data): Banner
    {
        $image = $data['image'] ?? null;
        unset($data['image']);

        $banner = Banner::withTrashed()->updateOrCreate(
            ['id' => $data['id'] ?? null],
            $data
        );

        if ($image) {
            $banner->clearMediaCollection('Banner');
            $banner->addMedia($image)->toMediaCollection('Banner');
        }

        return $banner->fresh();
    }
}
