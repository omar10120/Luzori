<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CenterUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'Super Admin', 'guard_name' => 'center_api'])->syncPermissions(Permission::where('guard_name', 'center_api')->pluck('id')->toArray());
    }
}
