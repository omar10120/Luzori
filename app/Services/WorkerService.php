<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\WorkerService as ModelsWorkerService;
use Illuminate\Support\Facades\DB;

class WorkerService
{
    public function getByService($service_id)
    {
        DB::beginTransaction();
        $workers = ModelsWorkerService::where('service_id', $service_id)->pluck('worker_id');
        $workerList = collect();
        foreach ($workers as $worker) {
            $workerInfo = Worker::select('id', 'name')->find($worker);
            if ($workerInfo) {
                $workerList->push($workerInfo);
            }
        }
        DB::commit();
        return $workerList;
    }
}
