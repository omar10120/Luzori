<?php
namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PackageResource;
use App\Http\Resources\UserPackageResource;
use App\Models\Center;
use App\Models\Package;
use App\Models\PackageServicePaid;
use App\Models\User as TenantUser;
use App\Models\UserPackage;
use App\Models\UserUsedPackage;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    /**
     * Get purchased packages for the authenticated user across all centers
     */
    public function userPurchased(Request $request)
    {
        $appUser = auth('sanctum')->user();
        if (!$appUser) {
            return MyHelper::responseJSON('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $allUserPackages = [];
        $centers = Center::whereNotNull('database')->get();

        foreach ($centers as $center) {
            try {
                Config::set('database.connections.mysql.database', $center->database);
                DB::purge('mysql');
                DB::reconnect('mysql');

                $tenantUser = TenantUser::where('email', $appUser->email)
                    ->orWhere('phone', $appUser->phone)
                    ->first();

                if ($tenantUser) {
                    $userPackages = UserPackage::where('user_id', $tenantUser->id)
                        ->with([
                            'package.translation', 
                            'package.packageServicePaid.service.translation', 
                            'package.packageServiceFree.service.translation', 
                            'usedPackages.service.translation'
                        ])
                        ->get();
                    
                    if ($userPackages->count() > 0) {
                        foreach ($userPackages as $pkg) {
                            $pkgArray = (new UserPackageResource($pkg))->resolve();
                            $pkgArray['center'] = [
                                'id' => $center->id,
                                'name' => $center->name,
                                'domain' => $center->domain,
                                'logo' => $center->logo ? url('storage/' . $center->logo) : null,
                            ];
                            $allUserPackages[] = $pkgArray;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore connection errors for inactive databases
                continue;
            }
        }

        // Restore central database connection
        Config::set('database.connections.mysql.database', env('DB_DATABASE'));
        DB::purge('mysql');
        DB::reconnect('mysql');

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $allUserPackages);
    }

    /**
     * Get purchased packages for the authenticated user at a center
     */
    public function index($center_id)
    {
        $center = Center::find($center_id);
        if (!$center || !$center->database) {
            return MyHelper::responseJSON('Center not found or database missing', Response::HTTP_NOT_FOUND);
        }
        if ($center->expire_date && now()->gt($center->expire_date)) {
            return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', Response::HTTP_BAD_REQUEST);
        }

        try {
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');

            $appUser = auth('sanctum')->user();
            if (!$appUser) {
                return MyHelper::responseJSON('Unauthorized', Response::HTTP_UNAUTHORIZED);
            }

            // Find tenant user to get their packages
            $tenantUser = TenantUser::where('email', $appUser->email)
                ->orWhere('phone', $appUser->phone)
                ->first();

            if (!$tenantUser) {
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, []);
            }

            $userPackages = UserPackage::where('user_id', $tenantUser->id)
                ->with([
                    'package.translation', 
                    'package.packageServicePaid.service.translation', 
                    'package.packageServiceFree.service.translation', 
                    'usedPackages.service.translation'
                ])
                ->get();
            

            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, UserPackageResource::collection($userPackages));
        } catch (\Exception $e) {
            Log::error('App Package Index Error: ' . $e->getMessage());
            return MyHelper::responseJSON('Error fetching user packages: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available packages to buy
     */
    public function available($center_id)
    {
        $center = Center::find($center_id);
        if (!$center || !$center->database) {
            return MyHelper::responseJSON('Center not found or database missing', Response::HTTP_NOT_FOUND);
        }
        if ($center->expire_date && now()->gt($center->expire_date)) {
            return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', Response::HTTP_BAD_REQUEST);
        }

        try {
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');

            $packages = Package::all();
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, PackageResource::collection($packages));
        } catch (\Exception $e) {
            return MyHelper::responseJSON('Error fetching available packages: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Purchase packages for a center (Simple version: no Sale/Booking records)
     */
    public function store(Request $request, $center_id)
    {
        $request->validate([
            'packages' => 'required|array|min:1',
            'packages.*.id' => 'required',
            'payment_type' => 'nullable|string',
        ]);

        $appUser = auth('sanctum')->user();
        if (!$appUser) {
            return MyHelper::responseJSON('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $center = Center::find($center_id);
        if (!$center || !$center->database) {
            return MyHelper::responseJSON('Center not found or database missing', Response::HTTP_NOT_FOUND);
        }
        if ($center->expire_date && now()->gt($center->expire_date)) {
            return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', Response::HTTP_BAD_REQUEST);
        }

        try {
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Find or create tenant user
            $tenantUser = TenantUser::where('email', $appUser->email)
                ->orWhere('phone', $appUser->phone)
                ->first();

            if (!$tenantUser) {
                $tenantUser = TenantUser::create([
                    'first_name' => $appUser->first_name,
                    'last_name' => $appUser->last_name,
                    'email' => $appUser->email,
                    'phone' => $appUser->phone ?? '-',
                    'country_code' => $appUser->country_code ?? '+',
                    'wallet' => 0,
                ]);
            }

            $totalPrice = 0;
            $packagesToAssign = [];
            foreach ($request->packages as $pkg) {
                $package = Package::find($pkg['id']);
                if (!$package) {
                    return MyHelper::responseJSON('Package not found: ' . $pkg['id'], Response::HTTP_NOT_FOUND);
                }
                $totalPrice += (float) $package->price;
                $packagesToAssign[] = $package;
            }

            // Wallet Deduction (from Central DB)
            $isPaidFromWallet = ($request->payment_type === 'wallet');
            if ($isPaidFromWallet) {
                $centralUser = DB::connection('central')->table('users')->where('id', $appUser->id)->first();
                if (!$centralUser || $centralUser->wallet < $totalPrice) {
                    return MyHelper::responseJSON(__('api.insufficient_balance') ?? 'Insufficient wallet balance', Response::HTTP_BAD_REQUEST);
                }
                DB::connection('central')->table('users')->where('id', $appUser->id)->decrement('wallet', $totalPrice);
            }

            // Assign Packages directly
            $createdPackages = [];
            foreach ($packagesToAssign as $package) {
                $createdPackages[] = UserPackage::create([
                    'user_id' => $tenantUser->id,
                    'package_id' => $package->id,
                    'price' => $package->price,
                    'status' => 'active',
                    'package_type' => $request->payment_type ?? 'cash',
                    'created_by' => $tenantUser->id,
                ]);
            }

            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_CREATED, UserPackageResource::collection($createdPackages));

        } catch (\Exception $e) {
            Log::error('App Package Simple Purchase Error: ' . $e->getMessage());
            return MyHelper::responseJSON('Purchase failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
