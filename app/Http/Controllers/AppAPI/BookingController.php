<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Center;
use App\Models\Service;
use App\Models\User as TenantUser;
use App\Services\SalesService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * List bookings for the authenticated app user (same mapping as store: central email/phone → tenant `users`, then bookings by `user_id` or `mobile`).
     *
     * Query: center_id (optional, filter one center), limit (optional, default 50, max 100 per center)
     */
    public function list(Request $request)
    {
        $appUser = $request->user();

        $centersQuery = Center::query()
            ->where('status', 'approve')
            ->whereNotNull('database');

        if ($request->filled('center_id')) {
            $centersQuery->whereKey((int) $request->query('center_id'));
        }

        $centers = $centersQuery->get();
        $limit = min(100, max(1, (int) $request->query('limit', 50)));

        $rows = [];
        $listDiagnostics = [];
        $centerMediaCache = [];
        $previousDb = config('database.connections.mysql.database');
        $appPhone = $this->normalizedAppPhone($appUser);

        try {
            foreach ($centers as $center) {
                if (empty($center->database)) {
                    $listDiagnostics[] = [
                        'center_id' => $center->id,
                        'step' => 'skip',
                        'reason' => 'empty_center_database',
                    ];
                    continue;
                }

                try {
                    if (!isset($centerMediaCache[$center->id])) {
                        $centerMediaCache[$center->id] = $this->centerMediaForApi($center);
                    }
                    $centerMedia = $centerMediaCache[$center->id];

                    Config::set('database.connections.mysql.database', $center->database);
                    DB::purge('mysql');
                    DB::reconnect('mysql');

                    $tenantUserIds = $this->tenantUserIdsMatchingAppUser($appUser);

                    if ($tenantUserIds->isEmpty() && $appPhone === null) {
                        $listDiagnostics[] = [
                            'center_id' => $center->id,
                            'tenant_database' => $center->database,
                            'step' => 'skip',
                            'reason' => 'no_email_or_phone_on_app_user',
                            'app_email' => $appUser->email,
                            'app_phone_raw' => $appUser->phone ?? null,
                            'tenant_user_ids' => [],
                        ];
                        continue;
                    }

                    $bookingsQuery = Booking::query()
                        ->where(function ($q) use ($tenantUserIds, $appPhone) {
                            if ($tenantUserIds->isNotEmpty()) {
                                $q->whereIn('user_id', $tenantUserIds);
                            }
                            if ($appPhone !== null) {
                                if ($tenantUserIds->isNotEmpty()) {
                                    $q->orWhere('mobile', $appPhone);   
                                } else {
                                    $q->where('mobile', $appPhone);
                                }
                            }
                        })
                        ->orderByDesc('id')
                        ->limit($limit);

                    Log::debug('AppAPI booking list SQL', [
                        'center_id' => $center->id,
                        'tenant_database' => $center->database,
                        'sql' => $bookingsQuery->toSql(),
                        'bindings' => $bookingsQuery->getBindings(),
                    ]);

                    $bookings = $bookingsQuery
                        ->with([
                            'details.service.translation',
                            'details.service.media',
                            'details.worker.media',
                            'branch',
                        ])
                        ->get();

                    $listDiagnostics[] = [
                        'center_id' => $center->id,
                        'tenant_database' => $center->database,
                        'step' => 'query',
                        'app_email' => $appUser->email,
                        'app_phone_for_match' => $appPhone,
                        'tenant_user_ids' => $tenantUserIds->values()->all(),
                        'bookings_fetched' => $bookings->count(),
                        'booking_ids' => $bookings->pluck('id')->values()->all(),
                    ];

                    foreach ($bookings as $booking) {
                        $rows[] = $this->serializeAppBookingForList($booking, $center, $centerMedia);
                    }
                } catch (\Throwable $e) {
                    $listDiagnostics[] = [
                        'center_id' => $center->id,
                        'tenant_database' => $center->database ?? null,
                        'step' => 'error',
                        'error' => $e->getMessage(),
                    ];
                    Log::warning('App booking list skipped center', [
                        'center_id' => $center->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            usort($rows, static function (array $a, array $b): int {
                return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
            });
        } finally {
            Config::set('database.connections.mysql.database', $previousDb);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        $summary = [
            'app_user_id' => $appUser->id,
            'app_email' => $appUser->email,
            'app_phone_for_match' => $appPhone,
            'centers_in_scope' => $centers->pluck('id')->values()->all(),
            'centers_in_scope_count' => $centers->count(),
            'limit' => $limit,
            'center_id_filter' => $request->query('center_id'),
            'bookings_returned' => count($rows),
            'per_center' => $listDiagnostics,
        ];

        Log::info('AppAPI booking list', $summary);

        $data = ['bookings' => $rows];
        // if (config('app.debug')) {
        //     $data['_list_debug'] = $summary;
        // }

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $data);
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @param  array{logo: string, primary_images: array<int, string>}  $centerMedia
     */
    private function serializeAppBookingForList(Booking $booking, Center $center, array $centerMedia): array
    {
        $first = $booking->details->first();
        $outside = $first && ($first->booking_source ?? 'inside_booking') === 'outside_booking';
        $status = $booking->deleted_at
            ? 'cancelled'
            : ($outside ? ($first->status ?? 'pending') : 'confirmed');
        $defaultImg = $this->defaultAppApiPlaceholderImage();

        return [
            'center_id' => $center->id,
            'center_name' => $center->name,
            'center_domain' => $center->domain,
            'center_logo' => $centerMedia['logo'],
            'center_primary_images' => $centerMedia['primary_images'],

            'id' => $booking->id,
            'booking_date' => $this->formatBookingListDateYmd($booking->getRawOriginal('booking_date')),
            'full_name' => $booking->full_name,
            'mobile' => $booking->mobile,
            'payment_type' => $booking->payment_type,
            'total_price' => (float) $booking->details->sum('price'),
            'branch' => $booking->branch ? [
                'id' => $booking->branch->id,
                'latitude' => $booking->branch->latitude,
                'longitude' => $booking->branch->longitude,
                'name' => $booking->branch->translate(app()->getLocale())?->name ?? $booking->branch->name,

            ] : null,
            
            'services' => $booking->details->map(function ($d) use ($defaultImg) {
                $service = $d->service;
                $worker = $d->worker;

                return [
                    'name' => $service?->translation?->name ?? $service?->name,
                    'image' => $service?->getFirstMediaUrl('Service') ?: $defaultImg,
                    'worker_name' => $worker?->name,
                    'worker_image' => $worker?->getFirstMediaUrl('Worker') ?: $defaultImg,
                    'price' => (float) $d->price,
                    'from_time' => $d->from_time,
                    'to_time' => $d->to_time,
                    'booking_source' => $d->booking_source ?? 'inside_booking',
                ];
            })->values()->all(),
            'booking_status' => $status,
            // CreatedAtTrait returns a string on non-admin URLs; use DB value for API output.
            'created_at' => $this->formatBookingListDateTime($booking->getRawOriginal('created_at')),
        ];
    }

    private function formatBookingListDateYmd(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('Y-m-d');
        }
        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return is_string($raw) && strlen($raw) >= 10 ? substr($raw, 0, 10) : null;
        }
    }

    private function formatBookingListDateTime(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if ($raw instanceof \DateTimeInterface) {
            return $raw->format('Y-m-d H:i:s');
        }
        try {
            return Carbon::parse($raw)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return is_string($raw) ? $raw : null;
        }
    }

    /**
     * Same media shape as {@see CenterResource} (central `centers` + Spatie collections).
     *
     * @return array{logo: string, primary_images: array<int, string>}
     */
    private function centerMediaForApi(Center $center): array
    {
        $fallback = $this->defaultAppApiPlaceholderImage();

        return [
            'logo' => $center->getFirstMediaUrl('Center') ?: $fallback,
            'primary_images' => $center->getMedia('PrimaryImage')->map(static function ($media) {
                return $media->getUrl();
            })->values()->all(),
        ];
    }

    private function defaultAppApiPlaceholderImage(): string
    {
        return asset('assets/img/avatars/1.png');
    }

    /**
     * Tenant DB `users.id` values that represent this app customer (email and/or phone).
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function tenantUserIdsMatchingAppUser($appUser): \Illuminate\Support\Collection
    {
        $query = TenantUser::query()->where(function ($w) use ($appUser) {
            $this->applyAppUserTenantUserMatch($w, $appUser);
        });

        return $query->pluck('id')->unique()->values();
    }

    private function findTenantUserMatchingAppUser($appUser): ?TenantUser
    {
        return TenantUser::query()
            ->where(function ($w) use ($appUser) {
                $this->applyAppUserTenantUserMatch($w, $appUser);
            })
            ->first();
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $w
     */
    private function applyAppUserTenantUserMatch($w, $appUser): void
    {
        $has = false;
        if (!empty($appUser->email)) {
            $w->where('email', $appUser->email);
            $has = true;
        }
        $phone = $this->normalizedAppPhone($appUser);
        if ($phone !== null) {
            $has ? $w->orWhere('phone', $phone) : $w->where('phone', $phone);
            $has = true;
        }
        if (!$has) {
            $w->whereRaw('0 = 1');
        }
    }

    private function normalizedAppPhone($appUser): ?string
    {
        $phone = $appUser->phone ?? null;
        if ($phone === null || $phone === '' || $phone === '-') {
            return null;
        }

        return $phone;
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
            'packages.*.id' => 'required', // Note: manual check after DB switch
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
            // Switch connection to tenant database
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // 2. Map AppUser to Tenant User (Customer) — same rules as list(): email or phone
            $tenantUser = $this->findTenantUserMatchingAppUser($appUser);

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

            // Process services
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
                        'client_name' => $tenantUser->name,
                        'client_mobile' => $tenantUser->phone,
                        'booking_source' => 'outside_booking',
                        'user_package_ids' => $svc['user_package_ids'] ?? null,
                    ];
                }
            }

            // Process package purchases
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
                        'type' => 'service', // SalesService expects 'service' type which can contain 'packages'
                        'packages' => $packageItems,
                        'client_mobile' => $tenantUser->phone,
                    ];
                }
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
                    'sale' => $sale->load([
                        'bookings.details.service.translation',
                        'bookings.details.service.media',
                        'bookings.details.worker.media',
                    ]),
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
