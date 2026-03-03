<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Center;
use App\Models\CenterUser;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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

    public function add($request)
    {
        try {
            $request['database'] = $request['domain'];
            $center = Center::create($request);
            if (isset($request['image'])) {
                $center->addMedia($request['image'])->toMediaCollection('Center');
            }
            if (isset($request['primary_image'])) {
                $center->addMedia($request['primary_image'])->toMediaCollection('PrimaryImage');
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
                    'WorkerSeeder'
                ];

                foreach ($seeders as $seeder) {
                    Artisan::call('db:seed', [
                        '--class' => $seeder,
                        '--database' => 'tenant',
                    ]);
                }

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

    public function edit($request)
    {
        DB::beginTransaction();
        try {
            $center = Center::withTrashed()->find($request['id']);
            if (isset($request['image'])) {
                $center->clearMediaCollection('Center');
                $center->addMedia($request['image'])->toMediaCollection('Center');
            }
            if (isset($request['primary_image'])) {
                $center->clearMediaCollection('PrimaryImage');
                $center->addMedia($request['primary_image'])->toMediaCollection('PrimaryImage');
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
