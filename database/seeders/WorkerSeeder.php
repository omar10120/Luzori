<?php

namespace Database\Seeders;

use App\Models\Worker;
use Illuminate\Database\Seeder;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $centerUsers = \App\Models\CenterUser::all();
        $centerUserEmails = $centerUsers->pluck('email')->filter()->toArray();
        $centerUserPhones = $centerUsers->pluck('phone')->filter()->toArray();

        // Clear existing workers that clash with center users (by email or phone)
        Worker::whereIn('email', $centerUserEmails)
            ->orWhereIn('phone', $centerUserPhones)
            ->forceDelete();

        // Also clear any marked as center user
        Worker::where('is_center_user', true)->forceDelete();

        $branchId = \App\Models\Branch::first()?->id ?? 1;
        $shiftId = \App\Models\Shift::first()?->id ?? 1;
        $allServiceIds = \App\Models\Service::pluck('id')->toArray();

        foreach ($centerUsers as $centerUser) {
            $worker = Worker::create([
                'name' => $centerUser->name,
                'email' => $centerUser->email,
                'country_code' => $centerUser->country_code,
                'phone' => $centerUser->phone,
                'branch_id' => $centerUser->branch_id ?? $branchId,
                'shift_id' => $shiftId,
                'has_commission' => 1,
                'status' => 1,
                'is_center_user' => true,
            ]);

            // Assign to all services
            foreach ($allServiceIds as $serviceId) {
                \App\Models\WorkerService::create([
                    'worker_id' => $worker->id,
                    'service_id' => $serviceId
                ]);
            }
        }

        // Keep the factory-generated workers for testing if needed
        // Worker::factory()->count(10)->hasServices(5)->create();
    }
}
