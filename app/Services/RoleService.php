<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function all($guard)
    {
        return Role::where('guard_name', $guard)->get();
    }

    public function find($id)
    {
        return Role::find($id);
    }

    public function add($request)
    {
        DB::beginTransaction();
        $role = Role::create($request);
        $role->syncPermissions($request['permissions']);
        DB::commit();
        return $role;
    }

    public function edit($request)
    {
        DB::beginTransaction();
        $role = Role::find($request['id']);
        $role->update($request);
        $role->syncPermissions($request['permissions']);
        DB::commit();
        return $role;
    }
}
