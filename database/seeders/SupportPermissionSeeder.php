<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Run: php artisan db:seed --class=SupportPermissionSeeder
 */
class SupportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $adminPerms = [
            ['name' => 'VIEW_SUPPORT_CHAT', 'name_ar' => 'عرض شات الدعم', 'group' => 'Support'],
            ['name' => 'UPDATE_SUPPORT_CHAT', 'name_ar' => 'إرسال رسائل الدعم', 'group' => 'Support'],
            ['name' => 'VIEW_SUPPORT_TICKETS', 'name_ar' => 'عرض تذاكر الدعم', 'group' => 'Support'],
            ['name' => 'UPDATE_SUPPORT_TICKETS', 'name_ar' => 'إدارة تذاكر الدعم', 'group' => 'Support'],
        ];

        $adminIds = [];
        foreach ($adminPerms as $perm) {
            $p = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'admin'],
                ['name_ar' => $perm['name_ar'], 'group' => $perm['group']]
            );
            $adminIds[] = $p->id;
        }

        $role = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($role) {
            $role->givePermissionTo(Permission::whereIn('id', $adminIds)->get());
        }

        $centerPerms = [
            ['name' => 'VIEW_SUPPORT_CHAT', 'name_ar' => 'عرض شات الدعم'],
            ['name' => 'UPDATE_SUPPORT_CHAT', 'name_ar' => 'إرسال رسائل الدعم'],
            ['name' => 'VIEW_SUPPORT_TICKETS', 'name_ar' => 'عرض تذاكر الدعم'],
            ['name' => 'CREATE_SUPPORT_TICKETS', 'name_ar' => 'إنشاء تذاكر الدعم'],
        ];

        foreach ($centerPerms as $perm) {
            foreach (['center', 'center_api'] as $guard) {
                Permission::firstOrCreate(
                    ['name' => $perm['name'], 'guard_name' => $guard],
                    ['name_ar' => $perm['name_ar'], 'group' => 'Support']
                );
            }
        }

        // Attach support permissions to every center_api role (menu uses this guard)
        $centerApiPerms = Permission::query()
            ->where('guard_name', 'center_api')
            ->whereIn('name', array_column($centerPerms, 'name'))
            ->get();

        if ($centerApiPerms->isNotEmpty()) {
            Role::query()
                ->where('guard_name', 'center_api')
                ->each(function (Role $role) use ($centerApiPerms) {
                    $role->givePermissionTo($centerApiPerms);
                });
        }
    }
}
