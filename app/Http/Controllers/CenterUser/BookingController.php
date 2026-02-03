<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\BookingDataTable;
use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\BookingRequest;
use App\Models\Discount;
use App\Models\Worker;
use App\Models\User;
use App\Models\UserUsedWallet;
use App\Models\Service;
use App\Services\BookingService;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\Setting;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class BookingController extends Controller
{
    private CRUDService $crudService;
    private $model = 'Booking';
    private $plural = 'bookings';
    private $indexRoute;
    private $updateOrCreateRoute;

    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(BookingDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
    }

    public function create(Request $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $title = __('general.add');
        $requestUrl = route($this->updateOrCreateRoute);

        $services = Service::with(['translation'])->get();
        $workers = Worker::all();
        $discounts = Discount::all();
        $paymentMethods = \App\Models\PaymentMethod::forBooking()->orWhereJsonContains('types', 'general')->get();

        $view = 'CenterUser.SubViews.' . $this->model . '.index';
        return view($view, compact('requestUrl', 'title', 'menu', 'menu_link', 'services', 'workers', 'discounts', 'paymentMethods'));
    }

    public function updateOrCreate(BookingRequest $request, BookingService $bookingService)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $newRequest = $request->only(
            'full_name',
            'mobile',
            'discount_id',
            'wallet_id',
            'membership_id',
            'payment_type',
            'service',
        );
        $item = $bookingService->add($newRequest);
        if ($item) {
            // return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, $item);
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.bookings.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getServicesByUser(Request $request)
    {
        $user = User::with(['memberships', 'wallets', 'services' => function ($q) {
            $q->with('service');
        }])->where('phone', $request->user_phone)->first();

        if ($user) {
            $services = $user->services->groupBy('service_id');
            
            // Get all wallets for this user via users_wallets table
            $userWallets = $user->wallets()
                ->with(['wallet' => function($query) {
                    // Ensure wallet is loaded even if soft deleted
                    $query->withTrashed();
                }])
                ->get();
            
            // Calculate remaining balance for each wallet after deductions from users_used_wallet
            // Filter out wallets with zero or negative balance
            $walletsWithBalance = $userWallets->map(function ($userWallet) use ($user) {
                $wallet = $userWallet->wallet;
                
                // Skip if wallet doesn't exist
                if (!$wallet) {
                    return null;
                }
                
                // Get original wallet amount
                $originalAmount = (float) ($wallet->amount ?? 0);
                
                // Calculate total used amount from users_used_wallet table
                // Sum all amounts where wallet_id matches AND user_id matches
                $totalUsed = UserUsedWallet::where('wallet_id', $wallet->id)
                    ->where('user_id', $user->id)
                    ->sum('amount');
                
                // Calculate remaining balance: original amount minus total used
                $remainingBalance = max(0, $originalAmount - (float) $totalUsed);
                
                // Add remaining balance to the wallet object
                // Make sure these properties are visible in JSON response
                $wallet->setAttribute('remaining_amount', $remainingBalance);
                $wallet->setAttribute('original_amount', $originalAmount);
                $wallet->makeVisible(['remaining_amount', 'original_amount']);
                
                return $userWallet;
            })
            ->filter(function ($userWallet) {
                // Filter out null wallets
                if (!$userWallet || !$userWallet->wallet) {
                    return false;
                }
                // Only show wallets with remaining balance > 0
                $remainingBalance = $userWallet->wallet->remaining_amount ?? 0;
                // Allow wallets with balance greater than 0 (not equal to 0)
                return $remainingBalance > 0;
            })
            ->values(); // Re-index the collection
            
            // Debug: Log wallets for troubleshooting
            \Log::info('Wallets for user', [
                'user_id' => $user->id,
                'user_phone' => $request->user_phone,
                'total_user_wallets' => $userWallets->count(),
                'wallets_with_balance_count' => $walletsWithBalance->count(),
                'wallets_detail' => $walletsWithBalance->map(function($uw) {
                    return [
                        'wallet_id' => $uw->wallet->id ?? null,
                        'code' => $uw->wallet->code ?? null,
                        'original_amount' => $uw->wallet->original_amount ?? null,
                        'remaining_amount' => $uw->wallet->remaining_amount ?? null,
                    ];
                })->toArray(),
                'all_user_wallets' => $userWallets->map(function($uw) {
                    return [
                        'user_wallet_id' => $uw->id ?? null,
                        'wallet_id' => $uw->wallet_id ?? null,
                        'wallet_loaded' => $uw->wallet ? true : false,
                        'wallet_code' => $uw->wallet->code ?? null,
                        'wallet_amount' => $uw->wallet->amount ?? null,
                    ];
                })->toArray()
            ]);
            
            $memberships = $user->memberships()->get();

            return response()->json([
                'status' => true,
                'user' => $user,
                'services' => $services,
                'wallets' => $walletsWithBalance,
                'memberships' => $memberships
            ]);
        } else {
            return response()->json(['status' => false]);
        }
    }

    public function getUsersByBranch(Request $request)
    {
        $users = Worker::where('branch_id', $request->branch_id)->get(['id', 'name']);
        return response()->json(['users' => $users]);
    }

    public function print(Request $request)
    {
        $booking = Booking::with(['created_by_user', 'user', 'wallet', 'details' => function ($q) {
            $q->with(['service' => function ($q) {
                $q->with(['translation']);
            }]);
        }])->withTrashed()->findOrFail($request->id);

        $options = [
            'format' => [80, 200], // Custom paper size (width, height) in points
            'orientation' => 'portrait', // or 'landscape'
            'margin-top' => 10,
            'margin-bottom' => 10,
            'margin-left' => 10,
            'margin-right' => 10,
        ];

        $invoice_info = Setting::where('key', SettingEnum::invoice_info->value)->first()->value;
        $template = (string)view('CenterUser.SubViews.Report.template.invoice_info', compact(
            'invoice_info',
        ));

        $view = 'CenterUser.SubViews.' . $this->model . '.print';
        $pdf = Pdf::loadView($view, compact('booking', 'template'), [], $options);
        return $pdf->stream('booking.pdf');
    }
}
