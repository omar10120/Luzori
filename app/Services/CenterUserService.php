<?php

namespace App\Services;

use App\Models\CenterUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CenterUserService
{
    public function all()
    {
        return CenterUser::withTrashed()->get();
    }

    public function find($id)
    {
        return CenterUser::withTrashed()->find($id);
    }

    public function add($request)
    {
        DB::beginTransaction();
        $centerUser = CenterUser::create($request);
        if (isset($request['image'])) {
            $centerUser->addMedia($request['image'])->toMediaCollection('CenterUser');
        }
        if (isset($request['role'])) {
            DB::table('model_has_roles')->insert([
                'role_id' => $request['role'],
                'model_type' => get_class($centerUser),
                'model_id' => $centerUser->id,
            ]);
            $centerUser->assignRole($request['role']);
        }
        DB::commit();
        return $centerUser;
    }

    public function edit($request)
    {
        DB::beginTransaction();
        $centerUser = CenterUser::withTrashed()->find($request['id']);
        if (isset($request['image'])) {
            $centerUser->clearMediaCollection('CenterUser');
            $centerUser->addMedia($request['image'])->toMediaCollection('CenterUser');
        }

        if (!isset($request['password'])) {
            unset($request['password']);
        }

        $centerUser->update($request);
        if (isset($request['role'])) {
            $centerUser->roles()->detach();
            DB::table('model_has_roles')->insert([
                'role_id' => $request['role'],
                'model_type' => get_class($centerUser),
                'model_id' => $centerUser->id,
            ]);
            // $centerUser->assignRole($request['role']);
        }
        DB::commit();
        return $centerUser;
    }

    public function changeLanguage($language_id, $id)
    {
        $centerUser = CenterUser::find($id);
        $centerUser->update([
            'language_id' => $language_id,
        ]);
        return $centerUser;
    }

    public function changePassword($request, $id)
    {
        $centerUser = CenterUser::find($id);

        if (!Hash::check($request['current_password'], $centerUser->password)) {
            return NULL;
        }

        $centerUser->update([
            'password' => $request['password'],
        ]);
        return $centerUser;
    }

    public function delete($id)
    {
        $centerUser = CenterUser::withTrashed()->find($id);
        $centerUser->tokens()->delete();
        $centerUser->fcmTokens()->delete();
        $centerUser->delete();
        return $centerUser;
    }

    public function changeStatusWeb($id)
    {
        $centerUser = CenterUser::find($id);
        if ($centerUser->statusWeb == 1) {
            $centerUser->update([
                'statusWeb' => 0
            ]);
        } else {
            $centerUser->update([
                'statusWeb' => 1
            ]);
        }
        return $centerUser;
    }
}
