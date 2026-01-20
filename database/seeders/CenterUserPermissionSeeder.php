<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class CenterUserPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // START USERS
        Permission::create([
            'name' => 'VIEW_USERS',
            'name_ar' => 'عرض المستخدمين',
            'group' => 'Users',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'SHOW_USERS',
            'name_ar' => 'عرض معلومات المستخدم',
            'group' => 'Users',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_USERS',
            'name_ar' => 'إضافة مستخدم',
            'group' => 'Users',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_USERS',
            'name_ar' => 'تعديل مستخدم',
            'group' => 'Users',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_USERS',
            'name_ar' => 'حذف مستخدم',
            'group' => 'Users',
            'guard_name' => 'center_api',
        ]);
        // END USERS

        // START CENTERUSERROLES
        Permission::create([
            'name' => 'VIEW_CENTERUSERROLES',
            'name_ar' => 'عرض ادوار مستخدمي الصالونات',
            'group' => 'Center User Roles',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_CENTERUSERROLES',
            'name_ar' => 'إضافة دور مستخدم الصالون',
            'group' => 'Center User Roles',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_CENTERUSERROLES',
            'name_ar' => 'تعديل دور مستخدم الصالون',
            'group' => 'Center User Roles',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_CENTERUSERROLES',
            'name_ar' => 'حذف دور مستخدم الصالون',
            'group' => 'Center User Roles',
            'guard_name' => 'center_api',
        ]);
        // END CENTERUSERROLES

        // START CENTERUSERS
        Permission::create([
            'name' => 'VIEW_CENTERUSERS',
            'name_ar' => 'عرض مستخدمين الصالون',
            'group' => 'Center Users',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_CENTERUSERS',
            'name_ar' => 'إضافة مستخدم صالون',
            'group' => 'Center Users',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_CENTERUSERS',
            'name_ar' => 'تعديل مستخدم صالون',
            'group' => 'Center Users',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_CENTERUSERS',
            'name_ar' => 'حذف مستخدم صالون',
            'group' => 'Center Users',
            'guard_name' => 'center_api',
        ]);
        // END CENTERUSERS

        // START NOTIFICATIONS
        Permission::create([
            'name' => 'VIEW_NOTIFICATIONS',
            'name_ar' => 'عرض الإشعارات',
            'group' => 'Notifications',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'SHOW_NOTIFICATIONS',
            'name_ar' => 'عرض معلومات الإشعار',
            'group' => 'Notifications',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_NOTIFICATIONS',
            'name_ar' => 'إضافة إشعار',
            'group' => 'Notifications',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_NOTIFICATIONS',
            'name_ar' => 'حذف إشعار',
            'group' => 'Notifications',
            'guard_name' => 'center_api',
        ]);
        // END NOTIFICATIONS

        // START BRANCHES
        Permission::create([
            'name' => 'VIEW_BRANCHES',
            'name_ar' => 'عرض الفروع',
            'group' => 'Branches',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_BRANCHES',
            'name_ar' => 'إضافة فرع',
            'group' => 'Branches',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_BRANCHES',
            'name_ar' => 'تعديل فرع',
            'group' => 'Branches',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_BRANCHES',
            'name_ar' => 'حذف فرع',
            'group' => 'Branches',
            'guard_name' => 'center_api',
        ]);
        // END BRANCHES

        // START WEEKSDAYS
        Permission::create([
            'name' => 'VIEW_WEEKSDAYS',
            'name_ar' => 'عرض أيام الأسبوع',
            'group' => 'Weeks Days',
            'guard_name' => 'center_api',
        ]);
        // END WEEKSDAYS

        // START SHIFTS
        Permission::create([
            'name' => 'VIEW_SHIFTS',
            'name_ar' => 'عرض الجلسات',
            'group' => 'Shifts',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_SHIFTS',
            'name_ar' => 'إضافة جلسة',
            'group' => 'Shifts',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_SHIFTS',
            'name_ar' => 'تعديل جلسة',
            'group' => 'Shifts',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_SHIFTS',
            'name_ar' => 'حذف جلسة',
            'group' => 'Shifts',
            'guard_name' => 'center_api',
        ]);
        // END SHIFTS

        // START SERVICES
        Permission::create([
            'name' => 'VIEW_SERVICES',
            'name_ar' => 'عرض الخدمات',
            'group' => 'Services',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_SERVICES',
            'name_ar' => 'إضافة خدمة',
            'group' => 'Services',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_SERVICES',
            'name_ar' => 'تعديل خدمة',
            'group' => 'Services',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_SERVICES',
            'name_ar' => 'حذف خدمة',
            'group' => 'Services',
            'guard_name' => 'center_api',
        ]);
        // END SERVICES

        // START VIEW_WORKERS
        Permission::create([
            'name' => 'VIEW_WORKERS',
            'name_ar' => 'عرض الموظفين',
            'group' => 'Workers',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_WORKERS',
            'name_ar' => 'إضافة موظف',
            'group' => 'Workers',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_WORKERS',
            'name_ar' => 'تعديل موظف',
            'group' => 'Workers',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_WORKERS',
            'name_ar' => 'حذف موظف',
            'group' => 'Workers',
            'guard_name' => 'center_api',
        ]);
        // END WORKERS

        // START VACATIONS
        Permission::create([
            'name' => 'VIEW_VACATIONS',
            'name_ar' => 'عرض إجازات الموظفين',
            'group' => 'Vacations',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_VACATIONS',
            'name_ar' => 'حذف إجازة',
            'group' => 'Vacations',
            'guard_name' => 'center_api',
        ]);
        // END VACATIONS

        // START MEMBERSHIPS
        Permission::create([
            'name' => 'VIEW_MEMBERSHIPS',
            'name_ar' => 'عرض العضوية',
            'group' => 'Memberships',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_MEMBERSHIPS',
            'name_ar' => 'إضافة عضوية',
            'group' => 'Memberships',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_MEMBERSHIPS',
            'name_ar' => 'تعديل عضوية',
            'group' => 'Memberships',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_MEMBERSHIPS',
            'name_ar' => 'حذف عضوية',
            'group' => 'Memberships',
            'guard_name' => 'center_api',
        ]);
        // END MEMBERSHIPS

        // START DISCOUNTS
        Permission::create([
            'name' => 'VIEW_DISCOUNTS',
            'name_ar' => 'عرض الحسومات',
            'group' => 'Discounts',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_DISCOUNTS',
            'name_ar' => 'إضافة حسم',
            'group' => 'Discounts',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_DISCOUNTS',
            'name_ar' => 'تعديل حسم',
            'group' => 'Discounts',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_DISCOUNTS',
            'name_ar' => 'حذف حسم',
            'group' => 'Discounts',
            'guard_name' => 'center_api',
        ]);
        // END DISCOUNTS

        // START WALLETS
        Permission::create([
            'name' => 'VIEW_WALLETS',
            'name_ar' => 'عرض الكوبونات',
            'group' => 'Wallets',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_WALLETS',
            'name_ar' => 'إضافة كوبونات',
            'group' => 'Wallets',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_WALLETS',
            'name_ar' => 'تعديل كوبونات',
            'group' => 'Wallets',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_WALLETS',
            'name_ar' => 'حذف كوبونات',
            'group' => 'Wallets',
            'guard_name' => 'center_api',
        ]);
        // END WALLETS

        // START USERS_WALLETS
        Permission::create([
            'name' => 'VIEW_USERS_WALLETS',
            'name_ar' => 'عرض كوبونات المستخدم',
            'group' => 'Users Wallets',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_USERS_WALLETS',
            'name_ar' => 'إضافة كوبون لمستخدم',
            'group' => 'Users Wallets',
            'guard_name' => 'center_api',
        ]);
        // END USERS_WALLETS

        // START PACKAGES
        Permission::create([
            'name' => 'VIEW_PACKAGES',
            'name_ar' => 'عرض الباقات',
            'group' => 'Packages',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_PACKAGES',
            'name_ar' => 'إضافة باقة',
            'group' => 'Packages',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_PACKAGES',
            'name_ar' => 'تعديل باقة',
            'group' => 'Packages',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_PACKAGES',
            'name_ar' => 'حذف باقة',
            'group' => 'Packages',
            'guard_name' => 'center_api',
        ]);

        // START PRODUCTS
        Permission::create([
            'name' => 'VIEW_PRODUCTS',
            'name_ar' => 'عرض المنتجات',
            'group' => 'Products',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_PRODUCTS',
            'name_ar' => 'إضافة منتج',
            'group' => 'Products',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_PRODUCTS',
            'name_ar' => 'تعديل منتج',
            'group' => 'Products',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_PRODUCTS',
            'name_ar' => 'حذف منتج',
            'group' => 'Products',
            'guard_name' => 'center_api',
        ]);
        // END PRODUCTS

        // START BUYPRODUCTS
        Permission::create([
            'name' => 'VIEW_BUYPRODUCTS',
            'name_ar' => 'عرض المنتجات المباعة',
            'group' => 'Buy Products',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_BUYPRODUCTS',
            'name_ar' => 'إضافة منتج مباع',
            'group' => 'Buy Products',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_BUYPRODUCTS',
            'name_ar' => 'حذف منتج مباع',
            'group' => 'Buy Products',
            'guard_name' => 'center_api',
        ]);
        // END BUYPRODUCTS

        // START BOOKINGS
        Permission::create([
            'name' => 'VIEW_BOOKINGS',
            'name_ar' => 'عرض الحجوزات',
            'group' => 'Bookings',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_BOOKINGS',
            'name_ar' => 'إضافة حجز',
            'group' => 'Bookings',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_BOOKINGS',
            'name_ar' => 'حذف الحجز',
            'group' => 'Bookings',
            'guard_name' => 'center_api',
        ]);
        // END BOOKINGS

        // START SALES
        Permission::create([
            'name' => 'VIEW_SALES',
            'name_ar' => 'عرض المبيعات',
            'group' => 'Sales',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'SHOW_SALES',
            'name_ar' => 'عرض تفاصيل المبيعات',
            'group' => 'Sales',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_SALES',
            'name_ar' => 'إضافة مبيعات',
            'group' => 'Sales',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_SALES',
            'name_ar' => 'تعديل مبيعات',
            'group' => 'Sales',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_SALES',
            'name_ar' => 'حذف مبيعات',
            'group' => 'Sales',
            'guard_name' => 'center_api',
        ]);
        // END SALES

        // START BOOKINGS WITH TIPS
        Permission::create([
            'name' => 'VIEW_BOOKING_WITH_TIPS',
            'name_ar' => 'Tips عرض الحجوزات مع',
            'group' => 'Booking With Tips',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_BOOKING_WITH_TIPS',
            'name_ar' => 'Tips إضافة حجز مع',
            'group' => 'Booking With Tips',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_BOOKING_WITH_TIPS',
            'name_ar' => 'Tips تعديل حجز مع',
            'group' => 'Booking With Tips',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_BOOKING_WITH_TIPS',
            'name_ar' => 'Tips حذف الحجز مع',
            'group' => 'Booking With Tips',
            'guard_name' => 'center_api',
        ]);
        // END BOOKINGS WITH TIPS

        // START REPORTS
        Permission::create([
            'name' => 'VIEW_DAILY_REPORTS',
            'name_ar' => 'عرض التقارير اليومية',
            'group' => 'Reports',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_SALES_REPORTS',
            'name_ar' => 'عرض تقارير المبيعات',
            'group' => 'Reports',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_STAFF_REPORTS',
            'name_ar' => 'عرض تقارير العاملين',
            'group' => 'Reports',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_COMMISSION_REPORTS',
            'name_ar' => 'عرض تقارير المعولة',
            'group' => 'Reports',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_TIPS_REPORTS',
            'name_ar' => 'عرض تقاير Tips',
            'group' => 'Reports',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_EXPENSE_REPORTS',
            'name_ar' => 'عرض تقارير المصروفات',
            'group' => 'Reports',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_COMMISSION',
            'name_ar' => 'عرض العمولات',
            'group' => 'Commission',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'COMMISSION_BOOKING_PERCENTAGE',
            'name_ar' => 'عمولة الحجوزات - نسبة مئوية',
            'group' => 'Commission',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'COMMISSION_BOOKING_FIXED',
            'name_ar' => 'عمولة الحجوزات - قيمة ثابتة',
            'group' => 'Commission',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'COMMISSION_PRODUCT_PERCENTAGE',
            'name_ar' => 'عمولة المنتجات - نسبة مئوية',
            'group' => 'Commission',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'COMMISSION_PRODUCT_FIXED',
            'name_ar' => 'عمولة المنتجات - قيمة ثابتة',
            'group' => 'Commission',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'COMMISSION_COUPON_PERCENTAGE',
            'name_ar' => 'عمولة الكوبونات - نسبة مئوية',
            'group' => 'Commission',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'COMMISSION_COUPON_FIXED',
            'name_ar' => 'عمولة الكوبونات - قيمة ثابتة',
            'group' => 'Commission',
            'guard_name' => 'center_api',
        ]);
        // END REPORTS

        // START INFOS
        Permission::create([
            'name' => 'VIEW_INFOS',
            'name_ar' => 'عرض معلومات التواصل',
            'group' => 'Contact Us',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_INFOS',
            'name_ar' => 'تعديل معلومات التواصل',
            'group' => 'Contact Us',
            'guard_name' => 'center_api',
        ]);
        // END INFOS

        // START PAGES
        Permission::create([
            'name' => 'VIEW_PAGES',
            'name_ar' => 'عرض الصفحات',
            'group' => 'Pages',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_PAGES',
            'name_ar' => 'تعديل الصفحات',
            'group' => 'Pages',
            'guard_name' => 'center_api',
        ]);
        // END PAGES

        // START SETTINGS
        Permission::create([
            'name' => 'VIEW_SETTINGS',
            'name_ar' => 'عرض الإعدادات',
            'group' => 'Settings',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_SETTINGS',
            'name_ar' => 'تعديل الإعدادات',
            'group' => 'Settings',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_EXPENSES',
            'name_ar' => 'عرض المصروفات',
            'group' => 'Expenses',
            'guard_name' => 'center_api',
        ]);
        // START PAYMENT METHODS
        Permission::create([
            'name' => 'VIEW_PAYMENT_METHODS',
            'name_ar' => 'عرض طرق الدفع',
            'group' => 'Payment Methods',
            'guard_name' => 'center_api',
        ]);
        // END PAYMENT METHODS
        Permission::create([
            'name' => 'CREATE_PAYMENT_METHODS',
            'name_ar' => 'إضافة طريقة دفع',
            'group' => 'Payment Methods',
            'guard_name' => 'center_api',
        ]);
        // END PAYMENT METHODS
        Permission::create([
            'name' => 'UPDATE_PAYMENT_METHODS',
            'name_ar' => 'تعديل طريقة دفع',
            'group' => 'Payment Methods',
            'guard_name' => 'center_api',
        ]);
        // END PAYMENT METHODS
        Permission::create([
            'name' => 'DELETE_PAYMENT_METHODS',
            'name_ar' => 'حذف طريقة دفع',
            'group' => 'Payment Methods',
            'guard_name' => 'center_api',
        ]);
        // END PAYMENT METHODS
        // END SETTINGS
        Permission::create([
            'name' => 'VIEW_BRANDS',
            'name_ar' => 'عرض الماركات',
            'group' => 'Brands',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_BRANDS',
            'name_ar' => 'إضافة ماركة',
            'group' => 'Brands',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_BRANDS',
            'name_ar' => 'تعديل ماركة',
            'group' => 'Brands',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_BRANDS',
            'name_ar' => 'حذف ماركة',
            'group' => 'Brands',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_PRODUCT_SUPPLIERS',
            'name_ar' => 'عرض الموردين',
            'group' => 'Product Suppliers',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_PRODUCT_SUPPLIERS',
            'name_ar' => 'إضافة مورد',
            'group' => 'Product Suppliers',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_PRODUCT_SUPPLIERS',
            'name_ar' => 'تعديل مورد',
            'group' => 'Product Suppliers',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_PRODUCT_SUPPLIERS',
            'name_ar' => 'حذف مورد',
            'group' => 'Product Suppliers',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_CATEGORIES',
            'name_ar' => 'عرض الفئات',
            'group' => 'Categories',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_CATEGORIES',
            'name_ar' => 'إضافة فئة',
            'group' => 'Categories',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_CATEGORIES',
            'name_ar' => 'تعديل فئة',
            'group' => 'Categories',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_CATEGORIES',
            'name_ar' => 'حذف فئة',
            'group' => 'Categories',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'VIEW_PRODUCT_SKUS',
            'name_ar' => 'عرض المنتجات المخصصة',
            'group' => 'Product Skus',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_PRODUCT_SKUS',
            'name_ar' => 'إضافة منتج مخصص',
            'group' => 'Product Skus',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_PRODUCT_SKUS',
            'name_ar' => 'تعديل منتج مخصص',
            'group' => 'Product Skus',
            'guard_name' => 'center_api',
        ]);
        // END PRODUCT SKUS

        // START STOCKTAKES
        Permission::create([
            'name' => 'VIEW_STOCKTAKES',
            'name_ar' => 'عرض جرد المخزون',
            'group' => 'Stocktakes',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'SHOW_STOCKTAKES',
            'name_ar' => 'عرض تفاصيل جرد المخزون',
            'group' => 'Stocktakes',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'CREATE_STOCKTAKES',
            'name_ar' => 'إضافة جرد مخزون',
            'group' => 'Stocktakes',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'UPDATE_STOCKTAKES',
            'name_ar' => 'تعديل جرد مخزون',
            'group' => 'Stocktakes',
            'guard_name' => 'center_api',
        ]);
        Permission::create([
            'name' => 'DELETE_STOCKTAKES',
            'name_ar' => 'حذف جرد مخزون',
            'group' => 'Stocktakes',
            'guard_name' => 'center_api',
        ]);
        // END STOCKTAKES
        
    }
}
