<?php

namespace App\Services;

use App\Models\User;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function add($request)
    {
        DB::beginTransaction();
        $user = User::create($request);
        if (isset($request['image'])) {
            $user->addMedia($request['image'])->toMediaCollection('User');
        }

        if (isset($request['fcm_token'])) {
            $messaging = Firebase::messaging();
            $messaging->subscribeToTopic('all', $request['fcm_token']);
            $messaging->subscribeToTopic('users', $request['fcm_token']);

            $user->fcmTokens()->attach([
                'token' => $request['fcm_token'],
            ]);
        }
		$user = User::find($user->id);
        DB::commit();
        return $user;
    }

    public function edit($request)
    {
        DB::beginTransaction();
        $user = User::withTrashed()->find($request['id']);
        if (isset($request['image'])) {
            $user->clearMediaCollection('User');
            $user->addMedia($request['image'])->toMediaCollection('User');
        }

        $user->update($request);
        DB::commit();
        return $user;
    }

    public function delete($id)
    {
        $user = User::withTrashed()->find($id);
        $user->tokens()->delete();
        $user->fcmTokens()->delete();
        $user->delete();
        return $user;
    }
}
