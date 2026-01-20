<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Helpers\MyHelper;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function all(RoleService $roleService)
    {
        $roles = $roleService->all('center_api');
        if ($roles) {
            $roles = RoleResource::collection($roles);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $roles);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
