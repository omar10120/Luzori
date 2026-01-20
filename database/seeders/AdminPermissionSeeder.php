<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // START CENTERROLES
        Permission::create([
            'name' => 'VIEW_CENTERROLES',
            'name_ar' => 'عرض أدوار الصالون',
            'group' => 'Center Roles',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'CREATE_CENTERROLES',
            'name_ar' => 'إضافة دور الصالون',
            'group' => 'Center Roles',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'UPDATE_CENTERROLES',
            'name_ar' => 'تعديل دور الصالون',
            'group' => 'Center Roles',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'DELETE_CENTERROLES',
            'name_ar' => 'حذف دور الصالون',
            'group' => 'Center Roles',
            'guard_name' => 'admin',
        ]);
        // END CENTERROLES

        // START CENTERS
        Permission::create([
            'name' => 'VIEW_CENTERS',
            'name_ar' => 'عرض الصالونات',
            'group' => 'Centers',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'CREATE_CENTERS',
            'name_ar' => 'إضافة صالون',
            'group' => 'Centers',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'UPDATE_CENTERS',
            'name_ar' => 'تعديل صالون',
            'group' => 'Centers',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'DELETE_CENTERS',
            'name_ar' => 'حذف صالون',
            'group' => 'Centers',
            'guard_name' => 'admin',
        ]);
        // END CENTERS

        // START ADMINROLES
        Permission::create([
            'name' => 'VIEW_ADMINROLES',
            'name_ar' => 'عرض أدوار المدراء',
            'group' => 'Admin Roles',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'CREATE_ADMINROLES',
            'name_ar' => 'إضافة دور مدير',
            'group' => 'Admin Roles',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'UPDATE_ADMINROLES',
            'name_ar' => 'تعديل دور مدير',
            'group' => 'Admin Roles',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'DELETE_ADMINROLES',
            'name_ar' => 'حذف دور مدير',
            'group' => 'Admin Roles',
            'guard_name' => 'admin',
        ]);
        // END ADMINROLES

        // START ADMINS
        Permission::create([
            'name' => 'VIEW_ADMINS',
            'name_ar' => 'عرض المدراء',
            'group' => 'Admins',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'CREATE_ADMINS',
            'name_ar' => 'إضافة مدير',
            'group' => 'Admins',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'UPDATE_ADMINS',
            'name_ar' => 'تعديل مدير',
            'group' => 'Admins',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'DELETE_ADMINS',
            'name_ar' => 'حذف مدير',
            'group' => 'Admins',
            'guard_name' => 'admin',
        ]);
        // END ADMINS

        // START INFOS
        Permission::create([
            'name' => 'VIEW_INFOS',
            'name_ar' => 'عرض معلومات التواصل',
            'group' => 'Contact Us',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'UPDATE_INFOS',
            'name_ar' => 'تعديل معلومات التواصل',
            'group' => 'Contact Us',
            'guard_name' => 'admin',
        ]);
        // END INFOS

        // START PAGES
        Permission::create([
            'name' => 'VIEW_PAGES',
            'name_ar' => 'عرض الصفحات',
            'group' => 'Pages',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'UPDATE_PAGES',
            'name_ar' => 'تعديل الصفحات',
            'group' => 'Pages',
            'guard_name' => 'admin',
        ]);
        // END PAGES

        // START SETTINGS
        Permission::create([
            'name' => 'VIEW_SETTINGS',
            'name_ar' => 'عرض الإعدادات',
            'group' => 'Settings',
            'guard_name' => 'admin',
        ]);
        Permission::create([
            'name' => 'UPDATE_SETTINGS',
            'name_ar' => 'تعديل الإعدادات',
            'group' => 'Settings',
            'guard_name' => 'admin',
        ]);
        // END SETTINGS
    }
}
