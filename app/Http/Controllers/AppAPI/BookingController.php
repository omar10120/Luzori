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
    
            // Sort all bookings by created_at (newest first)
            usort($rows, static function (array $a, array $b): int {
                return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
            });
        } finally {
            Config::set('database.connections.mysql.database', $previousDb);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }
    
        // ---- PAGINATION ----
        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);
        $total = count($rows);
        $offset = ($page - 1) * $perPage;
        $paginatedItems = array_slice($rows, $offset, $perPage);
    
        $paginator = new LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    
        $summary = [
            'app_user_id' => $appUser->id,
            'app_email' => $appUser->email,
            'app_phone_for_match' => $appPhone,
            'centers_in_scope' => $centers->pluck('id')->values()->all(),
            'centers_in_scope_count' => $centers->count(),
            'limit_per_center' => $limit,
            'center_id_filter' => $request->query('center_id'),
            'total_bookings_before_pagination' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'per_center' => $listDiagnostics,
        ];
    
        Log::info('AppAPI booking list', $summary);
    
        $responseData = $paginator->toArray();
    
        // Optionally include debug info (only in debug mode)
        if (config('app.debug')) {
            $responseData['_list_debug'] = $summary;
        }
    
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $responseData);
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