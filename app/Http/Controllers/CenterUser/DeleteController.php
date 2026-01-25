<?php

namespace App\Http\Controllers\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeleteController extends Controller
{
    public function __invoke(Request $request)
    {
        $can = 'DELETE_' . Str::upper(Str::plural($request->model));
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        if ($request->model == 'Role') {
            $modelNameSpace = "Spatie\\Permission\\Models\\" . Str::studly(Str::lower($request->model));
            if ($request->id == 1) {
                abort(404);
            }
        } else {
            $modelNameSpace = "App\\Models\\" . Str::studly(Str::lower($request->model));
        }

        $model = $request->withTrashed == 1 ? $modelNameSpace::withTrashed()->find($request->id) : $modelNameSpace::find($request->id);

        if ($request->operation == DeleteActionEnum::SOFT_DELETE->value) {
            $model->delete();
            return MyHelper::responseJSON(__('admin.de_active_done'), 200, $model);
        } else if ($request->operation == DeleteActionEnum::RESTORE_DELETED->value) {
            $model->restore();
            return MyHelper::responseJSON(__('admin.active_done'), 200, $model);
        } else { // DeleteActionEnum::FORCE_DELETE
            if (isset($request->media)) {
                $model->clearMediaCollection($model);
            }
            $model->forceDelete();
            return MyHelper::responseJSON(__('admin.done_delete_successfully'), 200, $model);
        }
    }
}
