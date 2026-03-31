<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\User as TenantUser;
use App\Models\Service;
use App\Models\Branch;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Store a new booking from the App
     */
    public function store(Request $request, SalesService $salesService)
    {
        $request->validate([
            'center_id' => 'required|exists:central.centers,id',
            'branch_id' => 'required',
            'services' => 'required|array|min:1',
            'services.*.id' => 'required',
            'services.*.worker_id' => 'required',
            'services.*.date' => 'required|date_format:Y-m-d',
            'services.*.from_time' => 'required|date_format:H:i',
            'services.*.to_time' => 'required|date_format:H:i',
            'payment_type' => 'nullable|string',
        ]);

        $appUser = $request->user();

        // 1. Find Center and Switch Database
        $center = Center::find($request->center_id);
        if (!$center || !$center->database) {
            return MyHelper::responseJSON('Center database configuration missing', Response::HTTP_BAD_REQUEST);
        }

        try {
            // Switch connection to tenant database
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // 2. Map AppUser to Tenant User (Customer)
            $tenantUser = TenantUser::where('email', $appUser->email)->first();

            if (!$tenantUser) {
                $tenantUser = TenantUser::create([
                    'first_name' => $appUser->first_name,
                    'last_name' => $appUser->last_name,
                    'email' => $appUser->email,
                    'phone' => $appUser->phone ?? '-',
                    'country_code' => $appUser->country_code ?? '+',
                    'branch_id' => $request->branch_id,
                    'wallet' => 0,
                ]);
            }

            // 3. Prepare Cart Data for SalesService
            $cartItems = [];
            foreach ($request->services as $svc) {
                $service = Service::find($svc['id']);
                if (!$service) {
                    return MyHelper::responseJSON('Service not found: ' . $svc['id'], Response::HTTP_NOT_FOUND);
                }

                $cartItems[] = [
                    'type' => 'service',
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'worker_id' => $svc['worker_id'],
                    'date' => $svc['date'],
                    'from_time' => $svc['from_time'],
                    'to_time' => $svc['to_time'],
                    'payment_type' => $request->payment_type ?? 'service_cash',
                    'client_name' => $tenantUser->name,
                    'client_mobile' => $tenantUser->phone,
                ];
            }

            $cartData = [
                'items' => $cartItems,
                'client_id' => $tenantUser->id,
                'worker_id' => null, // Overall sale worker (optional)
                'tip' => 0,
                'tax' => 0,
            ];

            // 4. Process Sale using SalesService
            // Note: SalesService uses auth('center_user') for created_by and branch_id.
            // We might need to handle this if it throws errors, but processSale seems to have some fallbacks.
            // Let's pass the branch_id in a way that respects the service's expectations if possible.
            
            $sale = $salesService->processSale($cartData, [
                'branch_id' => $request->branch_id,
                'created_by' => $tenantUser->id
            ]);

            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_CREATED, [
                'sale' => $sale->load(['bookings.details.service.translation', 'bookings.details.worker']),
            ]);

        } catch (\Exception $e) {
            Log::error('App Booking Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return MyHelper::responseJSON('Booking failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
