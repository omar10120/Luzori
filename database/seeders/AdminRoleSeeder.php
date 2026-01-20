<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'Super Admin', 'guard_name' => 'admin'])->syncPermissions(Permission::where('guard_name', 'admin')->pluck('id')->toArray());
    }
}
