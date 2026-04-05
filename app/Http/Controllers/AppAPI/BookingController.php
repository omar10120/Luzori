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

            // 3. Calculate Total Price and Check Wallet
            $totalPrice = 0;
            $cartItems = [];
            foreach ($request->services as $svc) {
                $service = Service::find($svc['id']);
                if (!$service) {
                    return MyHelper::responseJSON('Service not found: ' . $svc['id'], Response::HTTP_NOT_FOUND);
                }

                $totalPrice += (float) $service->price;

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
                    'booking_source' => 'outside_booking',
                ];
            }

            // Check and Deduct Wallet (from Central database)
            $isPaidFromWallet = ($request->payment_type === 'wallet');
            if ($isPaidFromWallet) {
                // We use DB::connection('central') because $appUser is from central
                // and we might have switched 'mysql' to tenant DB already.
                $centralUser = DB::connection('central')->table('users')->where('id', $appUser->id)->first();
                
                if (!$centralUser || $centralUser->wallet < $totalPrice) {
                    return MyHelper::responseJSON(__('api.insufficient_balance') ?? 'Insufficient wallet balance', Response::HTTP_BAD_REQUEST);
                }

                // Deduct from central wallet
                DB::connection('central')->table('users')->where('id', $appUser->id)->decrement('wallet', $totalPrice);
            }

            // 4. Prepare Cart Data for SalesService
            $cartData = [
                'items' => $cartItems,
                'client_id' => $tenantUser->id,
                'worker_id' => null, 
                'tip' => 0,
                'tax' => 0,
            ];

            try {
                // 5. Process Sale using SalesService
                $sale = $salesService->processSale($cartData, [
                    'branch_id' => $request->branch_id,
                    'created_by' => $tenantUser->id,
                    'center_id' => $center->id
                ]);

                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_CREATED, [
                    'sale' => $sale->load(['bookings.details.service.translation', 'bookings.details.worker']),
                ]);

            } catch (\Exception $saleException) {
                // ROLLBACK: If booking fails, refund the central wallet if it was deducted
                if ($isPaidFromWallet) {
                    DB::connection('central')->table('users')->where('id', $appUser->id)->increment('wallet', $totalPrice);
                }
                throw $saleException;
            }

        } catch (\Exception $e) {
            Log::error('App Booking Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return MyHelper::responseJSON('Booking failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
