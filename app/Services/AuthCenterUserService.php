<?php

namespace App\Services;

use App\Models\CenterUser;
use App\Models\Center;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthCenterUserService
{
    private function findUserAndSetDB($username, $type, &$activeCenter = null)
    {
        $originalDb = Config::get('database.connections.mysql.database');
        
        // If a valid domain header was passed, the middleware already set the DB.
        // We can check if the user exists in the currently active DB.
        try {
            $centeruser = CenterUser::withTrashed()->where($type, $username)->first();
            if ($centeruser) {
                // Determine the active center for this DB
                $activeCenter = Center::where('database', Config::get('database.connections.mysql.database'))->first();
                return $centeruser;
            }
        } catch (\Exception $e) {
            // Silently ignore and proceed to search all centers
        }

        // If not found in current DB, search across all centers
        $centers = Center::all();
        
        foreach ($centers as $center) {
            try {
                Config::set('database.connections.mysql.database', $center->database);
                DB::purge('mysql');
                DB::reconnect('mysql');
                
                $centeruser = CenterUser::withTrashed()->where($type, $username)->first();
                if ($centeruser) {
                    $activeCenter = $center;
                    return $centeruser;
                }
            } catch (\Exception $e) {
                Log::error("Failed to check db for center {$center->database}: " . $e->getMessage());
            }
        }
        
        // Restore original DB if not found anywhere
        Config::set('database.connections.mysql.database', $originalDb);
        DB::purge('mysql');
        DB::reconnect('mysql');
        
        return null;
    }

    public function login($request, &$reason = NULL)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        
        $activeCenter = null;
        $centeruser = $this->findUserAndSetDB($request['username'], $type, $activeCenter);

        if (!$centeruser) {
            $reason = 'INVALID_PASSWORD';
            return NULL;
        }

        if ($centeruser->trashed()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        }

        if (!Hash::check($request['password'], $centeruser->password)) {
            $reason = 'INVALID_PASSWORD';
            return NULL;
        }

        if (isset($request['fcm_token'])) {
            $centeruser->fcmTokens()->firstOrCreate([
                'token' => $request['fcm_token'],
            ]);
        }

        return [
            "token" => $centeruser->createToken("Device")->plainTextToken,
            "center_user" => $centeruser,
            "domain" => $activeCenter ? $activeCenter->domain : null
        ];
    }

    public function forget($username, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $username)) ? 'email' : 'phone';
        $centeruser = $this->findUserAndSetDB($username, $type);

        if (!$centeruser) {
            $reason = 'CENTER_USER_NOT_FOUND';
            return NULL;
        }

        if ($centeruser->trashed()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        }

        $rand = random_int(1111, 9999);
        $rand = 1111;
        //send email or sms

        $centeruser->update([
            'verification_code' => $rand
        ]);
        return $centeruser;
    }

    public function checkCode($request, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        $centeruser = $this->findUserAndSetDB($request['username'], $type);

        if (!$centeruser) {
            $reason = 'CENTER_USER_NOT_FOUND';
            return NULL;
        }

        if ($centeruser->trashed()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        }

        if ($centeruser->verification_code != $request['verification_code'] || $request['verification_code'] == 0) {
            $reason = 'CODE_NOT_MATCH';
            return NULL;
        }

        $centeruser->update([
            'verification_code' => 1
        ]);
        return $centeruser;
    }

    public function reset($request, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        $activeCenter = null;
        $centeruser = $this->findUserAndSetDB($request['username'], $type, $activeCenter);

        if (!$centeruser) {
            $reason = 'CENTER_USER_NOT_FOUND';
            return NULL;
        }

        if ($centeruser->trashed()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        }

        if ($centeruser->verification_code == 1) {
            $centeruser->update([
                'verification_code' => 0,
                'password' => $request['password'],
            ]);

            if (isset($request['fcm_token'])) {
                $centeruser->fcmTokens()->firstOrCreate([
                    'token' => $request['fcm_token']
                ]);
            }

            return [
                "token" => $centeruser->createToken("Device")->plainTextToken,
                "center_user" => $centeruser,
                "domain" => $activeCenter ? $activeCenter->domain : null
            ];
        }

        $reason = 'ACCOUNT_NOT_READY_TO_RESET';
        return NULL;
    }
}
