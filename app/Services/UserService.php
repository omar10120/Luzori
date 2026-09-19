<?php

namespace App\Services;

use App\Models\User;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function add($request)
    {
        DB::beginTransaction();

        // Normalize is_default to a real boolean
        $isDefault = !empty($request['is_default']);
        $request['is_default'] = $isDefault;

        // If this user is being created as default, unset everyone else first
        if ($isDefault) {
            $this->resetOtherDefaults(null);
        }

        $user = User::create($request);

        if (isset($request['image'])) {
            $user->addMedia($request['image'])->toMediaCollection('User');
        }

        Log::info('User created', ['user' => $user]);

        if (isset($request['fcm_token'])) {
            $messaging = Firebase::messaging();
            $messaging->subscribeToTopic('all', $request['fcm_token']);
            $messaging->subscribeToTopic('users', $request['fcm_token']);

            $user->fcmTokens()->attach([
                'token' => $request['fcm_token'],
            ]);
        }

        $centerUser = auth('center_api')->user() ?? auth('center_user')->user();
        if ($centerUser) {
            $user->branch_id = $centerUser->branch_id;
            $user->save();
        }

        $user = User::find($user->id);
        DB::commit();
        return $user;
    }

    public function edit($request)
    {
        DB::beginTransaction();

        $user = User::withTrashed()->find($request['id']);

        // Normalize is_default to a real boolean
        $isDefault = !empty($request['is_default']);
        $request['is_default'] = $isDefault;

        // If this user is now default, unset everyone else (except this user)
        if ($isDefault) {
            $this->resetOtherDefaults($user->id);
        }

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

    /**
     * Set is_default = 0 for every user except (optionally) the given id.
     */
    private function resetOtherDefaults(?int $exceptId = null): void
    {
        $query = User::query()->where('is_default', true);

        if (!is_null($exceptId)) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update(['is_default' => false]);
    }
}