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
                // Attach center logo if uploaded. If no image is provided, the UI falls back to a default logo.
                $center->addMedia($request['image'])->toMediaCollection('Center');
            }
            $center->assignRole($request['role']);

            $dbName = $center->database;
            
            \Log::info('Attempting to create database', [
                'dbName' => $dbName,
                'dbHost' => env('DB_HOST', '127.0.0.1'),
                'dbUsername' => env('DB_USERNAME', 'luzori')
            ]);
            
            try {
                $new_db = DB::statement("CREATE DATABASE `$dbName`");
            } catch (\Exception $dbError) {
                \Log::error('Database creation error', [
                    'dbName' => $dbName,
                    'error' => $dbError->getMessage(),
                    'code' => $dbError->getCode()
                ]);
                $new_db = false;
            }
            
            \Log::info('Database creation result', [
                'dbName' => $dbName,
                'success' => $new_db,
                'error' => $new_db ? 'No error' : 'Database creation failed'
            ]);
            
            if ($new_db) {
                // Configure tenant connection with all credentials
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
                Artisan::call('db:seed', [
                    '--class' => 'WeekDaySeeder',
                    '--database'  => 'tenant',
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'CenterUserPermissionSeeder',
                    '--database'  => 'tenant',
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'CenterUserRoleSeeder',
                    '--database'  => 'tenant',
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'LanguageSeeder',
                    '--database'  => 'tenant',
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'InfoSeeder',
                    '--database'  => 'tenant',
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'PageSeeder',
                    '--database'  => 'tenant',
                ]);
                Artisan::call('db:seed', [
                    '--class' => 'SettingSeeder',
                    '--database'  => 'tenant',
                ]);
                
                // Artisan::call('db:seed', [
                //     '--class' => 'PaymentMethodSeeder',
                //     '--database'  => 'tenant',
                // ]);
                $centerUser = new CenterUser($request);
                $centerUser->setConnection('tenant');
                $centerUser->save();
                // $centerUser->addMedia($request['image'])->toMediaCollection('CenterUser');
                DB::connection('tenant')->table('model_has_roles')->insert([
                    'role_id' => 1,
                    'model_type' => get_class($centerUser),
                    'model_id' => $centerUser->id,
                ]);
                $configOutput = [];
                $configReturnCode = 0;
                exec("php artisan config:show database.connections.tenant 2>&1", $configOutput, $configReturnCode);
                $configInfo = "Tenant Config:\n" . implode("\n", $configOutput);
                \log::info($configInfo);
                \log::info($configReturnCode);
                \log::info("dbName: ".$dbName);
                
                // $centerUser->assignRole($request['role']);
            }
            return $center;
        } catch (Exception $e) {
            // Log the config info for debugging
            \Log::error('Center creation failed', [
                'error' => $e->getMessage(),
                'config_info' => $configInfo,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function edit($request)
    {
        DB::beginTransaction();
        $center = Center::withTrashed()->find($request['id']);
        if (isset($request['image'])) {
            $center->clearMediaCollection('Center');
            $center->addMedia($request['image'])->toMediaCollection('Center');
        }

        if (!isset($request['password'])) {
            unset($request['password']);
        }

        $center->update($request);
        if (isset($request['role'])) {
            $center->roles()->detach();
            $center->assignRole($request['role']);
        }
        DB::commit();
        return $center;
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
