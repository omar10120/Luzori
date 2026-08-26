<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds admin notification permissions and attaches them to Super Admin.
 * Run: php artisan db:seed --class=AdminNotificationPermissionSeeder
 */
class AdminNotificationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            ['name' => 'VIEW_NOTIFICATIONS', 'name_ar' => 'عرض الإشعارات'],
            ['name' => 'CREATE_NOTIFICATIONS', 'name_ar' => 'إنشاء إشعار'],
            ['name' => 'UPDATE_NOTIFICATIONS', 'name_ar' => 'تعديل الإشعارات'],
            ['name' => 'DELETE_NOTIFICATIONS', 'name_ar' => 'حذف الإشعارات'],
            ['name' => 'UPDATE_FIREBASE_SETTINGS', 'name_ar' => 'تعديل إعدادات Firebase'],
        ];

        $ids = [];
        foreach ($perms as $perm) {
            $p = Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'admin'],
                ['name_ar' => $perm['name_ar'], 'group' => 'Notifications']
            );
            $ids[] = $p->id;
        }

        $role = Role::where('name', 'Super Admin')->where('guard_name', 'admin')->first();
        if ($role) {
            $role->givePermissionTo(Permission::whereIn('id', $ids)->get());
        }
    }
}
