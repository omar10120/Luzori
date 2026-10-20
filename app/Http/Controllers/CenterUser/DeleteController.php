<?php

namespace App\Http\Controllers\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Services\NotifiesCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DeleteController extends Controller
{
    use NotifiesCenter;

    public function __invoke(Request $request)
    {
        $can = 'DELETE_' . Str::upper(Str::plural($request->model));
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        if ($request->model == 'Sale') {
            return MyHelper::responseJSON(__('general.sale_otp_required'), 403);
        }

        if ($request->model == 'Role') {
            $modelNameSpace = "Spatie\\Permission\\Models\\" . Str::studly(Str::lower($request->model));
            if ($request->id == 1) {
                abort(404);
            }
        } else {
            $modelNameSpace = "App\\Models\\" . $request->model;
            if (!class_exists($modelNameSpace)) {
                $modelNameSpace = "App\\Models\\" . Str::studly(Str::lower($request->model));
            }
        }

        $model = $request->withTrashed == 1 ? $modelNameSpace::withTrashed()->find($request->id) : $modelNameSpace::find($request->id);

        $workerSnapshot = null;
        if ($request->model === 'Worker' && $model) {
            $workerSnapshot = [
                'id' => (int) $model->id,
                'name' => (string) ($model->name ?? ''),
            ];
        }

        if ($request->operation == DeleteActionEnum::SOFT_DELETE->value) {
            $model->delete();
            $this->notifyWorkerDeleted($workerSnapshot);
            return MyHelper::responseJSON(__('admin.de_active_done'), 200, $model);
        } else if ($request->operation == DeleteActionEnum::RESTORE_DELETED->value) {
            $model->restore();
            return MyHelper::responseJSON(__('admin.active_done'), 200, $model);
        } else { // DeleteActionEnum::FORCE_DELETE
            if (isset($request->media)) {
                $model->clearMediaCollection($model);
            }
            $model->forceDelete();
            $this->notifyWorkerDeleted($workerSnapshot);
            return MyHelper::responseJSON(__('admin.done_delete_successfully'), 200, $model);
        }
    }

    private function notifyWorkerDeleted(?array $worker): void
    {
        if (!$worker) {
            return;
        }

        $name = $worker['name'];
        $this->notifyCenter('system', [
            'en' => [
                'title' => 'Worker deleted',
                'text' => 'Worker "' . $name . '" has been deleted.',
            ],
            'ar' => [
                'title' => 'تم حذف الموظف',
                'text' => 'تم حذف الموظف "' . $name . '".',
            ],
        ], [
            'worker_id' => (string) $worker['id'],
        ]);
    }
}
