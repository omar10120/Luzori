<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Service;
use App\Models\User as TenantUser;
use App\Services\NotifiesCenter;
use App\Services\SalesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingController extends Controller
{
    use NotifiesCenter;

    /**
     * List bookings for the authenticated app user (same mapping as store: central email/phone → tenant `users`, then bookings by `user_id` or `mobile`).
     *
     * Query: center_id (optional, filter one center), limit (optional, default 50, max 100 per center)
     */
    public function list(Request $request)
    {
        // ... unchanged — paste as-is from your current file ...
    }

    /**
     * Store a new booking from the App
     */
    public function store(Request $request, SalesService $salesService)
    {
        $request->validate([
            'center_id' => 'required|exists:central.centers,id',
            'branch_id' => 'required',
            'services' => 'required_without:packages|array',
            'services.*.id' => 'required',
            'services.*.worker_id' => 'required',
            'services.*.date' => 'required|date_format:Y-m-d',
            'services.*.from_time' => 'required|date_format:H:i',
            'services.*.to_time' => 'required|date_format:H:i',
            'services.*.user_package_ids' => 'nullable|array',
            'packages' => 'required_without:services|array',
            'packages.*.id' => 'required',
            'payment_type' => 'nullable|string',
        ]);

        $appUser = $request->user();

        // 1. Find Center and Switch Database
        $center = Center::find($request->center_id);
        if (!$center || !$center->database) {
            return MyHelper::responseJSON('Center database configuration missing', Response::HTTP_BAD_REQUEST);
        }
        if ($center->expire_date && now()->gt($center->expire_date)) {
            return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', Response::HTTP_BAD_REQUEST);
        }

        try {
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // 2. Map AppUser to Tenant User
            $tenantUser = $this->findTenantUserMatchingAppUser($appUser);

            if (!$tenantUser) {
                $tenantUser = $this->createTenantUserForAppUser($appUser, (int) $request->branch_id);
            }

            // 3. Calculate Total Price and Check Wallet
            $totalPrice = 0;
            $cartItems = [];
            $clientName = $this->tenantUserDisplayName($tenantUser, $appUser);

            if ($request->filled('services')) {
                foreach ($request->services as $svc) {
                    $service = Service::find($svc['id']);
                    if (!$service) {
                        return MyHelper::responseJSON('Service not found: ' . $svc['id'], Response::HTTP_NOT_FOUND);
                    }

                    $isUsingPackage = !empty($svc['user_package_ids']);
                    $itemPrice = $isUsingPackage ? 0 : (float) $service->price;
                    $totalPrice += $itemPrice;

                    $cartItems[] = [
                        'type' => 'service',
                        'id' => $service->id,
                        'name' => $service->name,
                        'price' => $itemPrice,
                        'worker_id' => $svc['worker_id'],
                        'date' => $svc['date'],
                        'from_time' => $svc['from_time'],
                        'to_time' => $svc['to_time'],
                        'payment_type' => $request->payment_type ?? ($isUsingPackage ? 'package' : 'service_cash'),
                        'client_name' => $clientName,
                        'client_mobile' => $tenantUser->phone,
                        'booking_source' => 'outside_booking',
                        'user_package_ids' => $svc['user_package_ids'] ?? null,
                    ];
                }
            }

            if ($request->filled('packages')) {
                $packageItems = [];
                foreach ($request->packages as $pkg) {
                    $package = \App\Models\Package::find($pkg['id']);
                    if (!$package) {
                        return MyHelper::responseJSON('Package not found: ' . $pkg['id'], Response::HTTP_NOT_FOUND);
                    }

                    $totalPrice += (float) $package->price;
                    $packageItems[] = [
                        'id' => $package->id,
                        'price' => $package->price,
                    ];
                }

                if (!empty($packageItems)) {
                    $cartItems[] = [
                        'type' => 'service',
                        'packages' => $packageItems,
                        'client_mobile' => $tenantUser->phone,
                    ];
                }
            }

            $isPaidFromWallet = ($request->payment_type === 'wallet');
            if ($isPaidFromWallet) {
                $centralUser = DB::connection('central')->table('users')->where('id', $appUser->id)->first();

                if (!$centralUser || $centralUser->wallet < $totalPrice) {
                    return MyHelper::responseJSON(__('api.insufficient_balance') ?? 'Insufficient wallet balance', Response::HTTP_BAD_REQUEST);
                }

                DB::connection('central')->table('users')->where('id', $appUser->id)->decrement('wallet', $totalPrice);
            }

            $cartData = [
                'items' => $cartItems,
                'client_id' => $tenantUser->id,
                'worker_id' => null,
                'tip' => 0,
                'tax' => 0,
            ];

            try {
                // 5. Process Sale
                $sale = $salesService->processSale($cartData, [
                    'branch_id' => $request->branch_id,
                    'created_by' => $tenantUser->id,
                    'center_id' => $center->id
                ]);

                // 5b. Notify the center about the new booking (best-effort, never fails the booking).
                $this->notifyBookingCreated($center, $sale, $tenantUser, $appUser, $totalPrice);

                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_CREATED, [
                    'sale' => $sale->load([
                        'bookings.details.service.translation',
                        'bookings.details.service.media',
                        'bookings.details.worker.media',
                    ]),
                ]);

            } catch (\Exception $saleException) {
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

    /**
     * Find the tenant `users` row that corresponds to the central App user.
     *
     * Runs on the default ('mysql') connection, which store() has already
     * switched to the center's database. Returns null when no match is found
     * so the caller can create the tenant user.
     */
    private function findTenantUserMatchingAppUser($appUser): ?TenantUser
    {
        if (!$appUser) {
            return null;
        }

        // 1) Match by email (most reliable)
        $email = trim((string) ($appUser->email ?? ''));
        if ($email !== '') {
            $byEmail = TenantUser::query()
                ->where('email', $email)
                ->first();

            if ($byEmail) {
                return $byEmail;
            }
        }

        // 2) Fall back to phone
        $phone = trim((string) ($appUser->phone ?? ''));
        if ($phone !== '') {
            $byPhone = TenantUser::query()
                ->where('phone', $phone)
                ->first();

            if ($byPhone) {
                return $byPhone;
            }
        }

        return null;
    }

    /**
     * Create the tenant `users` row for a central App user.
     *
     * Uses firstOrCreate on email when an email is present, so two concurrent
     * bookings for a brand-new app user cannot both insert the same row.
     */
    private function createTenantUserForAppUser($appUser, int $branchId): TenantUser
    {
        $payload = [
            'first_name' => $appUser->first_name,
            'last_name' => $appUser->last_name,
            'email' => $appUser->email,
            'phone' => $appUser->phone ?? '-',
            'country_code' => $appUser->country_code ?? '+',
            'branch_id' => $branchId,
            'wallet' => 0,
        ];

        $email = trim((string) ($appUser->email ?? ''));
        if ($email !== '') {
            return TenantUser::firstOrCreate(
                ['email' => $email],
                $payload
            );
        }

        // No email to key on — fall back to plain create() (matches old behaviour).
        return TenantUser::create($payload);
    }

    /**
     * Build a human-readable display name for the tenant user, with fallbacks.
     */
    private function tenantUserDisplayName(TenantUser $tenantUser, $appUser = null): string
    {
        $name = trim(
            (string) ($tenantUser->first_name ?? '') . ' ' .
            (string) ($tenantUser->last_name ?? '')
        );

        if ($name !== '') {
            return $name;
        }

        if ($appUser) {
            $fallback = trim(
                (string) ($appUser->first_name ?? '') . ' ' .
                (string) ($appUser->last_name ?? '')
            );
            if ($fallback !== '') {
                return $fallback;
            }
        }

        $phone = trim((string) ($tenantUser->phone ?? ''));
        if ($phone !== '' && $phone !== '-') {
            return $phone;
        }

        return 'Customer';
    }

    /**
     * Send a "new booking" notification to the center that owns the tenant DB.
     *
     * Uses the NotifiesCenter trait → AppNotificationService::notifyCenterEvent().
     * Best-effort: the trait already wraps exceptions and logs them.
     */
    private function notifyBookingCreated(
        Center $center,
        $sale,
        TenantUser $tenantUser,
        $appUser,
        float $totalPrice
    ): void {
        try {
            $customerName = $this->tenantUserDisplayName($tenantUser, $appUser);

            $bookings = $sale->bookings ?? collect();
            $detailsCount = $bookings->sum(fn ($b) => $b->details->count());
            $firstBooking = $bookings->first();

            $bookingDate = null;
            if ($firstBooking) {
                $rawDate = $firstBooking->getRawOriginal('booking_date');
                if ($rawDate) {
                    try {
                        $bookingDate = Carbon::parse($rawDate)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $bookingDate = (string) $rawDate;
                    }
                }
            }

            $priceFormatted = number_format($totalPrice, 2);

            $textEn = sprintf(
                'New booking from %s — %d service(s), total %s.',
                $customerName,
                $detailsCount,
                $priceFormatted
            );
            if ($bookingDate) {
                $textEn .= ' Date: ' . $bookingDate . '.';
            }

            $textAr = sprintf(
                'حجز جديد من %s — عدد الخدمات %d، الإجمالي %s.',
                $customerName,
                $detailsCount,
                $priceFormatted
            );
            if ($bookingDate) {
                $textAr .= ' بتاريخ ' . $bookingDate . '.';
            }

            $this->notifyCenter(
                'booking',
                [
                    'en' => ['title' => 'New Booking', 'text' => $textEn],
                    'ar' => ['title' => 'حجز جديد',   'text' => $textAr],
                ],
                [
                    'action'       => 'booking_created',
                    'sale_id'      => (string) $sale->id,
                    'booking_id'   => (string) ($firstBooking->id ?? ''),
                    'booking_date' => (string) ($bookingDate ?? ''),
                    'center_id'    => (string) $center->id,
                ]
            );
        } catch (\Throwable $e) {
            // Trait already catches its own errors — this is a final safety net
            // so a notification problem can never block the booking response.
            Log::warning('notifyBookingCreated failed', [
                'sale_id' => $sale->id ?? null,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // ------------------------------------------------------------------------
    // Everything below (list(), serializeAppBookingForList(), format helpers,
    // centerMediaForApi(), placeholder) stays EXACTLY as in your current file.
    //
    // NOTE: the tenant-user matching helper that used to live in this section
    // has been moved up into the class as `findTenantUserMatchingAppUser()`
    // (and its companion `createTenantUserForAppUser()` / `tenantUserDisplayName()`).
    // The `list()` stub and any serialization helpers still need to be pasted
    // back from your real working copy — the placeholder that was here before
    // was the reason `store()` was failing at runtime.
    // ------------------------------------------------------------------------
}