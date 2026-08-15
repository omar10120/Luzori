<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserUsedPackage;
use App\Models\UserUsedWallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CustomerSearchService
{
    public function listForSelect(?int $selectedId = null, int $limit = 50): Collection
    {
        $users = User::query()
            ->with('media')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($selectedId && !$users->contains('id', $selectedId)) {
            $selected = User::with('media')->find($selectedId);
            if ($selected) {
                $users->prepend($selected);
            }
        }

        return $users;
    }

    public function search(string $term, int $page = 1, int $perPage = 30): array
    {
        $term = trim($term);
        $columns = ['id', 'first_name', 'last_name', 'email', 'country_code', 'phone', 'branch_id'];

        $query = User::query()
            ->select($columns)
            ->with('media');

        if ($term !== '') {
            $like = '%' . $term . '%';
            $digits = preg_replace('/\D+/', '', $term);

            $query->where(function ($q) use ($like, $digits) {
                $q->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(IFNULL(first_name, ''), ' ', IFNULL(last_name, '')) LIKE ?", [$like])
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('phone', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(IFNULL(country_code, ''), IFNULL(phone, '')) LIKE ?", [$like]);

                if ($digits !== '') {
                    $q->orWhere('phone', 'LIKE', '%' . $digits . '%')
                        ->orWhereRaw(
                            "CONCAT(IFNULL(REPLACE(country_code, '+', ''), ''), IFNULL(phone, '')) LIKE ?",
                            ['%' . $digits . '%']
                        );
                }
            })->orderBy('first_name');
        } else {
            $query->orderByDesc('id');
        }

        $paginator = $query->paginate($perPage, $columns, 'page', $page);

        return [
            'results' => $paginator->getCollection()->map(fn (User $user) => $this->map($user))->values(),
            'pagination' => ['more' => $paginator->hasMorePages()],
        ];
    }

    public function find(int $id): array
    {
        return $this->map(User::with('media')->findOrFail($id));
    }

    public function map(User $user): array
    {
        $phone = $user->phone ?? '';
        $fullPhone = $user->full_phone ?? $phone;
        $image = $user->getFirstMediaUrl(class_basename($user)) ?: asset('assets/img/avatars/1.png');

        return [
            'id' => $user->id,
            'text' => trim($user->name . ' - ' . ($fullPhone ?: ($user->email ?? ''))),
            'name' => $user->name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $phone,
            'full_phone' => $fullPhone,
            'country_code' => $user->country_code,
            'image' => $image,
            'branch_id' => $user->branch_id,
        ];
    }

    public function getBookingProfile(string $phone): array
    {
        $phone = trim($phone);
        if ($phone === '') {
            return ['status' => false];
        }

        $cacheKey = 'customer_booking_profile_' . md5($phone);

        return Cache::remember($cacheKey, 180, function () use ($phone) {
            return $this->buildBookingProfile($phone);
        });
    }

    private function buildBookingProfile(string $phone): array
    {
        $digits = preg_replace('/\D+/', '', $phone);

        $user = User::query()
            ->select('id', 'first_name', 'last_name', 'phone', 'country_code', 'email')
            ->where(function ($q) use ($phone, $digits) {
                $q->where('phone', $phone)
                    ->orWhereRaw("CONCAT(IFNULL(country_code, ''), phone) = ?", [$phone]);
                if ($digits !== '') {
                    $q->orWhere('phone', $digits)
                        ->orWhereRaw(
                            "CONCAT(IFNULL(REPLACE(country_code, '+', ''), ''), phone) = ?",
                            [$digits]
                        );
                }
            })
            ->with([
                'memberships:id,user_id,membership_no,percent',
                'wallets.wallet',
                'packages' => function ($q) {
                    $q->where('status', 'active')->with([
                        'package.packageServicePaid.service.translation',
                        'package.packageServiceFree.service.translation',
                    ]);
                },
                'services.service.translation',
                'services.service.category.translation',
            ])
            ->first();

        if (!$user) {
            return ['status' => false];
        }

        $walletIds = $user->wallets->pluck('wallet_id')->filter();
        $usedWalletAmounts = $walletIds->isEmpty()
            ? collect()
            : UserUsedWallet::query()
                ->where('user_id', $user->id)
                ->whereIn('wallet_id', $walletIds)
                ->selectRaw('wallet_id, SUM(amount) as used_amount')
                ->groupBy('wallet_id')
                ->pluck('used_amount', 'wallet_id');

        $wallets = $user->wallets->map(function ($userWallet) use ($usedWalletAmounts) {
            $used = (float) ($usedWalletAmounts[$userWallet->wallet_id] ?? 0);

            return [
                'id' => $userWallet->id,
                'remaining_balance' => (float) $userWallet->amount - $used,
                'wallet' => [
                    'id' => $userWallet->wallet_id,
                    'code' => $userWallet->wallet->code ?? '',
                ],
            ];
        })->values();

        $packageIds = $user->packages->pluck('id');
        $usedPackageRows = $packageIds->isEmpty()
            ? collect()
            : UserUsedPackage::query()
                ->whereIn('user_package_id', $packageIds)
                ->selectRaw('user_package_id, service_id, is_free, COUNT(*) as used_count')
                ->groupBy('user_package_id', 'service_id', 'is_free')
                ->get()
                ->groupBy('user_package_id');

        $packages = $user->packages->map(function ($userPackage) use ($usedPackageRows) {
            $usedServices = $usedPackageRows->get($userPackage->id, collect());
            $remainingServices = [];

            if ($userPackage->package) {
                foreach ($userPackage->package->packageServicePaid->groupBy('service_id') as $serviceId => $services) {
                    $usedRow = $usedServices->first(fn ($row) => (int) $row->service_id === (int) $serviceId && (int) $row->is_free === 0);
                    $remaining = $services->count() - (int) ($usedRow->used_count ?? 0);
                    if ($remaining > 0) {
                        $service = $services->first()->service;
                        $remainingServices[] = [
                            'service_id' => (int) $serviceId,
                            'remaining' => $remaining,
                            'is_free' => 0,
                            'service' => ['id' => (int) $serviceId, 'name' => $service->name ?? ''],
                        ];
                    }
                }

                foreach ($userPackage->package->packageServiceFree->groupBy('service_id') as $serviceId => $services) {
                    $usedRow = $usedServices->first(fn ($row) => (int) $row->service_id === (int) $serviceId && (int) $row->is_free === 1);
                    $remaining = $services->count() - (int) ($usedRow->used_count ?? 0);
                    if ($remaining > 0) {
                        $service = $services->first()->service;
                        $remainingServices[] = [
                            'service_id' => (int) $serviceId,
                            'remaining' => $remaining,
                            'is_free' => 1,
                            'service' => ['id' => (int) $serviceId, 'name' => $service->name ?? ''],
                        ];
                    }
                }
            }

            return [
                'id' => $userPackage->id,
                'package_type' => $userPackage->package_type,
                'package' => $userPackage->package ? [
                    'id' => $userPackage->package->id,
                    'name' => $userPackage->package->name ?? '',
                ] : null,
                'remaining_services' => $remainingServices,
            ];
        })->filter(fn ($userPackage) => count($userPackage['remaining_services']) > 0)->values();

        $services = $user->services->groupBy('service_id')->map(function ($group) {
            return $group->map(function ($item) {
                $service = $item->service;

                return [
                    'service' => [
                        'id' => $service->id ?? null,
                        'name' => $service->name ?? '',
                        'category' => [
                            'name' => $service->category->name ?? null,
                            'translation' => [
                                'name' => $service->category->translation->name ?? null,
                            ],
                        ],
                    ],
                ];
            })->values();
        });

        return [
            'status' => true,
            'services' => $services,
            'wallets' => $wallets,
            'memberships' => $user->memberships->map(fn ($m) => [
                'id' => $m->id,
                'membership_no' => $m->membership_no,
                'percent' => $m->percent,
            ])->values(),
            'packages' => $packages,
        ];
    }
}
