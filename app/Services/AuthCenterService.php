<?php

namespace App\Services;

use App\Models\Center;
use Illuminate\Support\Facades\Hash;

class AuthCenterService
{
    public function login($request, &$reason = NULL)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        if (Center::onlyTrashed()->where($type, $request['username'])->exists()) {
            $reason = 'CENTER_BLOCKED';
            return NULL;
        } else {
            $center = Center::where($type, $request['username'])->first();

            if (!Hash::check($request['password'], $center->password)) {
                $reason = 'INVALID_PASSWORD';
                return NULL;
            } else {
                auth('center_user')->user()?->tokens()?->delete();

                if (isset($request['fcm_token'])) {
                    $center->fcmTokens()->firstOrCreate([
                        'token' => $request['fcm_token'],
                    ]);
                }

                return [
                    "token" => $center->createToken("Device")->plainTextToken,
                    "center" => $center,
                ];
            }
        }
    }

    public function forget($username, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $username)) ? 'email' : 'phone';
        $center = Center::withTrashed()->where($type, $username)->first();

        if ($center->trashed()) {
            $reason = 'CENTER_BLOCKED';
            return NULL;
        } else {
            $rand = random_int(1111, 9999);
            $rand = 1111;
            //send email or sms

            $center->update([
                'verification_code' => $rand
            ]);
            return $center;
        }
    }

    public function checkCode($request, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        $center = Center::withTrashed()->where($type, $request['username'])->first();

        if ($center->trashed()) {
            $reason = 'CENTER_BLOCKED';
            return NULL;
        } else {
            if ($center->verification_code != $request['verification_code'] || $request['verification_code'] == 0) {
                $reason = 'CODE_NOT_MATCH';
                return NULL;
            } else {
                $center->update([
                    'verification_code' => 1
                ]);
                return $center;
            }
        }
    }

    public function reset($request, &$reason)
    {
        $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request['username'])) ? 'email' : 'phone';
        $center = Center::withTrashed()->where($type, $request['username'])->first();

        if ($center->trashed()) {
            $reason = 'CENTER_BLOCKED';
            return NULL;
        } else {
            if ($center->verification_code == 1) {
                $center->update([
                    'verification_code' => 0,
                    'password' => $request['password'],
                ]);

                if (isset($request['fcm_token'])) {
                    $center->fcmTokens()->firstOrCreate([
                        'token' => $request['fcm_token']
                    ]);
                }

                return [
                    "token" => $center->createToken("Device")->plainTextToken,
                    "center" => $center
                ];
            } else {
                $reason = 'ACCOUNT_NOT_READY_TO_RESET';
                return NULL;
            }
        }
    }
}
