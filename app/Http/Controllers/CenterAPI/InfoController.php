<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Helpers\MyHelper;
use App\Http\Resources\InfoResource;
use App\Services\CRUDService;
use Illuminate\Http\Response;

class InfoController extends Controller
{
    public function all(CRUDService $cRUDService)
    {
        $infos = $cRUDService->first('Info');
        if ($infos) {
            $infos = InfoResource::make($infos);
            return MyHelper::responseJSON(__('api.infoExists'), Response::HTTP_OK, $infos);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
