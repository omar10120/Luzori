<?php

namespace App\Services;

use App\Models\AppUser;
use Illuminate\Support\Facades\DB;

class AppUserService
{
    private $model;

    public function __construct(AppUser $model)
    {
        $this->model = $model;
    }

    public function add($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->create($request);
            if (isset($request['image'])) {
                $user->addMedia($request['image'])->toMediaCollection('PrimaryImage');
            }
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("AppUser add error: " . $e->getMessage());
            return false;
        }
    }

    public function edit($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->find($request['id']);
            $user->update($request);
            if (isset($request['image'])) {
                $user->clearMediaCollection('PrimaryImage');
                $user->addMedia($request['image'])->toMediaCollection('PrimaryImage');
            }
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("AppUser edit error: " . $e->getMessage());
            return false;
        }
    }
}
