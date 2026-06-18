<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Services\GetBanksService;
use Illuminate\Http\Response;
use Throwable;
use Illuminate\Support\Facades\Log;

class GetBanksController extends Controller
{
    public function index(GetBanksService $getBanksService)
    {
        try {
            return response()->json($getBanksService->fetch());
        } catch (Throwable $e) {
            Log::error('GetBanksController getBanks exception: ' . $e->getMessage());

            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

  
}
