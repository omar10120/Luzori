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
            $query = Worker::select('id', 'name')->where('id', $worker);
            if ($branch_id) {
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
