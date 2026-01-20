<?php

namespace App\Services;

use App\Models\CenterUser;
use Illuminate\Support\Facades\Hash;

class AuthCenterUserService
{
    public function login($request, &$reason = NULL)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        if (CenterUser::onlyTrashed()->where($type, $request['username'])->exists()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        } else {
            $centeruser = CenterUser::where($type, $request['username'])->first();

            if (!Hash::check($request['password'], $centeruser->password)) {
                $reason = 'INVALID_PASSWORD';
                return NULL;
            } else {
                if (isset($request['fcm_token'])) {
                    $centeruser->fcmTokens()->firstOrCreate([
                        'token' => $request['fcm_token'],
                    ]);
                }

                return [
                    "token" => $centeruser->createToken("Device")->plainTextToken,
                    "center_user" => $centeruser,
                ];
            }
        }
    }

    public function forget($username, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $username)) ? 'email' : 'phone';
        $centeruser = CenterUser::withTrashed()->where($type, $username)->first();

        if ($centeruser->trashed()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        } else {
            $rand = random_int(1111, 9999);
            $rand = 1111;
            //send email or sms

            $centeruser->update([
                'verification_code' => $rand
            ]);
            return $centeruser;
        }
    }

    public function checkCode($request, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        $centeruser = CenterUser::withTrashed()->where($type, $request['username'])->first();

        if ($centeruser->trashed()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        } else {
            if ($centeruser->verification_code != $request['verification_code'] || $request['verification_code'] == 0) {
                $reason = 'CODE_NOT_MATCH';
                return NULL;
            } else {
                $centeruser->update([
                    'verification_code' => 1
                ]);
                return $centeruser;
            }
        }
    }

    public function reset($request, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        $centeruser = CenterUser::withTrashed()->where($type, $request['username'])->first();

        if ($centeruser->trashed()) {
            $reason = 'CENTER_USER_BLOCKED';
            return NULL;
        } else {
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
                    "center_user" => $centeruser
                ];
            } else {
                $reason = 'ACCOUNT_NOT_READY_TO_RESET';
                return NULL;
            }
        }
    }
}
