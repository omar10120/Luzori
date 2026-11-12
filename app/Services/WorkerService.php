<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\WorkerService as ModelsWorkerService;
use Illuminate\Support\Facades\DB;

class WorkerService
{
    public function getByService($service_id, $branch_id = null)
    {
        DB::beginTransaction();
        $workers = ModelsWorkerService::where('service_id', $service_id)->pluck('worker_id');
        $workerList = collect();
        foreach ($workers as $worker) {
            $query = Worker::select('id', 'name', 'phone', 'is_center_user', 'is_professional', 'salary', 'visa_start_date', 'visa_end_date', 'percentage')->where('id', $worker);
            if ($branch_id && get_user_role() != 1) {
                $query->where('branch_id', $branch_id);
            }
            $workerInfo = $query->first();
            if ($workerInfo) {
                $workerList->push($workerInfo);
            }
        }
        DB::commit();
        return $workerList;
    }
}
