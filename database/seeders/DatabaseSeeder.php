<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            AdminPermissionSeeder::class,
            AdminRoleSeeder::class,
            AdminSeeder::class,
            CenterPermissionSeeder::class,
            CenterRoleSeeder::class,
            LanguageSeeder::class,
            PageSeeder::class,
            InfoSeeder::class,
            SettingSeeder::class,
            // BranchSeeder::class,
            // WeekDaySeeder::class,
            // ShiftSeeder::class,
            // ServiceSeeder::class,
            // WorkerSeeder::class,
            // DiscountSeeder::class,
            // PackageSeeder::class,
            // PackageServiceFreeSeeder::class,
            // PackageServicePaidSeeder::class,
            // ProductSeeder::class,
            // CenterUserPermissionSeeder::class,
            // CenterUserRoleSeeder::class,
            // UserSeeder::class,
            // ServiceUserSeeder::class,
        ]);
    }
}
