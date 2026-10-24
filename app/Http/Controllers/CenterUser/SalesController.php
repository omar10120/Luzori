<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\SalesDataTable;
use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Center;
use App\Models\CenterUser;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Sale;
use App\Models\User;
use App\Models\Worker;
use App\Services\InvoiceSettingsService;
use App\Services\SalesService;
use App\Services\AppNotificationService;
use App\Services\SaleOtpService;
use App\Services\CustomerSearchService;
use App\Models\PaymentMethod;
use App\Traits\CategoryTreeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\NotifiesCenter; 
class SalesController extends Controller
{
    use CategoryTreeTrait;
    use NotifiesCenter;

    private $model = 'Sale';
    private $plural = 'sales';
    private $indexRoute;
    private $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        
    }

    /**
     * Display sales list (DataTable)
     */
    public function index(SalesDataTable $dataTable)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render('CenterUser.SubViews.Sale.list', compact('title'));
    }

    /**
     * Show cart interface
     */
    public function cart(Request $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('general.add') . ' ' . __('locale.' . $this->plural);

        // AJAX save only — never render the full cart page on POST.
        // Empty `cart: []` is omitted by jQuery, so do not require the cart key.
        if ($request->isMethod('post')) {
            $cart = session('sales_cart', [
                'items' => [],
                'client_id' => null,
                'worker_id' => null,
                'tip' => 0,
                'tax' => 0,
                'payment_type' => null,
            ]);

            if ($request->boolean('replace_items') || ($request->exists('cart') && is_array($request->input('cart')))) {
                $posted = $request->input('cart', []);
                $cart['items'] = is_array($posted) ? array_values(array_filter($posted)) : [];
            }

            if ($request->exists('client_id')) {
                $cart['client_id'] = $request->filled('client_id') ? $request->input('client_id') : null;
            }

            session(['sales_cart' => $cart]);

            return MyHelper::responseJSON('Cart saved', Response::HTTP_OK, [
                'client_id' => $cart['client_id'],
                'items_count' => count($cart['items'] ?? []),
            ]);
        }

        // Get cart from session
        $cart = session('sales_cart', [
            'items' => [],
            'client_id' => null,
            'worker_id' => null,
            'tip' => 0,
            'tax' => 0,
            'payment_type' => null,
        ]);

        $centerUser = auth('center_user')->user();

        $isAdmin = ((int) get_user_role() === 1);
        
        // Use service to get all dependencies
        $data = $this->salesService->getCartData($cart, $centerUser, $isAdmin);
        
        $services = $data['services'];
        $products = $data['products'];
        $discounts = $data['discounts'];
        $packages = $data['packages'];
        $paymentMethods = $data['paymentMethods'];
        $productPaymentMethods = $data['productPaymentMethods'];
        $walletPaymentMethods = $data['walletPaymentMethods'];
        $wallets = $data['wallets'];
        $selectedUser = $data['selectedUser'];
        $workers = $data['workers'];
        $destinationBranchId = $data['branchId'];

        $categoriesJson = $this->getFormattedCategories();

        // Prebuild JS maps once (avoid heavy Blade @foreach loops)
        $servicesData = $services->mapWithKeys(function ($service) {
            return [
                $service->id => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => (float) ($service->price ?? 0),
                    'category_id' => $service->category_id,
                    'has_commission' => (bool) $service->has_commission,
                ],
            ];
        });

        $productsData = $products->mapWithKeys(function ($product) use ($destinationBranchId) {
            $price = $product->retail_price && $product->retail_price > 0
                ? (float) $product->retail_price
                : (float) ($product->supply_price ?? 0);
            $branchStock = $product->productBranches->firstWhere('branch_id', $destinationBranchId);
            return [
                $product->id => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $price,
                    'supply_price' => (float) ($product->supply_price ?? 0),
                    'retail_price' => (float) ($product->retail_price ?? 0),
                    'stock_quantity' => $branchStock?->stock_quantity ?? 0,

                ],
            ];
        });

        $view = 'CenterUser.SubViews.' . $this->model . '.cart';
        return view($view, compact(
            'services',
            'products',
            'workers',
            'discounts',
            'packages',
            'paymentMethods',
            'productPaymentMethods',
            'walletPaymentMethods',
            'wallets',
            'selectedUser',
            'cart',
            'title',
            'menu',
            'menu_link',
            'categoriesJson',
            'centerUser',
            'servicesData',
            'productsData',
            'destinationBranchId'
        ));
    }

    public function notifyMainBranchOutOfStock(Request $request, AppNotificationService $notificationService)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $hasSuperAdmin = CenterUser::whereHas('roles', function ($query) {
            $query->where('roles.id', 1);
        })->exists();

        if (!$hasSuperAdmin) {
            return response()->json([
                'message' => __('field.no_super_admin_available'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $product = Product::with('translation')->findOrFail($validated['product_id']);
        $center = Center::where('database', config('database.connections.mysql.database'))->first()
            ?? $this->resolveActiveCenter();

        if (!$center) {
            return response()->json([
                'message' => __('api.unknownError'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $productNameEn = $product->translate('en')?->name ?: $product->name;
        $productNameAr = $product->translate('ar')?->name ?: $product->name;
        $notification = $notificationService->sendAdminNotification([
            'target_type' => 'centers',
            'type' => 'stock_alert',
            'send_push' => false,
            'recipients' => [(string) $center->id],
            'en' => [
                'title' => 'Product out of stock',
                'text' => 'The quantity is out of stock for product: ' . $productNameEn,
            ],
            'ar' => [
                'title' => 'منتج غير متوفر',
                'text' => 'الكمية غير متوفرة للمنتج: ' . $productNameAr,
            ],
        ]);

        if (!$notification) {
            return response()->json([
                'message' => __('admin.an_error_occurred'),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => __('field.out_of_stock_notification_sent'),
        ]);
    }

    /**
     * Lazy-load coupons for the cart wallet tab.
     */
    public function cartWallets()
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }
    
        // Pick the authenticated user from either guard
        $centerUser = auth('center_api')->user() ?? auth('center_user')->user();
    
        // Role 1 = admin / super admin → see ALL wallets (no branch filter)
        $isAdmin = ((int) get_user_role() === 1);
    
        $wallets  = $this->salesService->getCartWallets($centerUser, $isAdmin);
        $currency = get_currency();
    
        $rows = $wallets->map(function ($wallet) use ($currency) {
            $clients = $wallet->users
                ->map(fn ($uw) => $uw->user->name ?? null)
                ->filter()
                ->values()
                ->all();
    
            return [
                'id'              => $wallet->id,
                'code'            => $wallet->code,
                'clients'         => $clients,
                'amount'          => number_format((float) $wallet->amount, 2) . ' ' . $currency,
                'invoiced_amount' => number_format((float) $wallet->invoiced_amount, 2) . ' ' . $currency,
                'start_at'        => $wallet->start_at ? \Carbon\Carbon::parse($wallet->start_at)->format('Y-m-d') : '-',
                'end_at'          => $wallet->end_at   ? \Carbon\Carbon::parse($wallet->end_at)->format('Y-m-d')   : '-',
                'created_by'      => $wallet->created_by_user->name ?? '-',
                'used'            => (bool) $wallet->used,
                'raw' => [
                    'amount'          => $wallet->amount,
                    'invoiced_amount' => $wallet->invoiced_amount,
                    'start_at'        => $wallet->start_at,
                    'end_at'          => $wallet->end_at,
                ],
            ];
        });
    
        return response()->json(['data' => $rows]);
    }
    /**
     * Lazy-load packages for the cart package tab.
     */
    public function cartPackages()
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $packages = $this->salesService->getCartPackages();
        $currency = get_currency();

        $rows = $packages->map(function ($package) use ($currency) {
            return [
                'id' => $package->id,
                'name' => $package->name,
                'price' => number_format((float) ($package->price ?? 0), 2) . ' ' . $currency,
                'paid_services' => $package->packageServicePaid
                    ->map(fn ($ps) => $ps->service->name ?? null)
                    ->filter()
                    ->values()
                    ->all(),
                'free_services' => $package->packageServiceFree
                    ->map(fn ($ps) => $ps->service->name ?? null)
                    ->filter()
                    ->values()
                    ->all(),
                'users_count' => (int) ($package->users_packages_count ?? 0),
            ];
        });

        return response()->json(['data' => $rows]);
    }

    public function searchCustomers(Request $request, CustomerSearchService $customerSearchService)
    {
        return response()->json($customerSearchService->search(
            (string) ($request->get('q') ?: $request->get('term', '')),
            max(1, (int) $request->get('page', 1))
        ));
    }

    public function getCustomer($id, CustomerSearchService $customerSearchService)
    {
        return response()->json($customerSearchService->find((int) $id));
    }

    /**
     * Add service to cart
     */
    public function addServiceToCart(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'worker_id' => 'required|exists:workers,id',
            'date' => 'required|date',
            'from_time' => 'required|date_format:H:i',
            'to_time' => 'required|date_format:H:i|after:from_time',
        ]);

        $service = Service::find($request->service_id);
        $cart = session('sales_cart', ['items' => []]);

        // Check if service already in cart
        foreach ($cart['items'] as $item) {
            if ($item['type'] === 'service' && $item['id'] == $request->service_id) {
                return MyHelper::responseJSON('Service already in cart', Response::HTTP_BAD_REQUEST);
            }
        }

        $cart['items'][] = [
            'type' => 'service',
            'id' => $service->id,
            'name' => $service->name,
            'price' => $service->price,
            'worker_id' => $request->worker_id,
            'date' => $request->date,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
        ];

        session(['sales_cart' => $cart]);
        return MyHelper::responseJSON('Service added to cart', Response::HTTP_OK, ['cart' => $cart]);
    }

    /**
     * Add product to cart
     */
    public function addProductToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::find($request->product_id);
        $cart = session('sales_cart', ['items' => []]);
        $branchId = $this->salesService->resolveSaleBranchId($cart);

        if (!$branchId) {
            return MyHelper::responseJSON('No branch found for this sale', Response::HTTP_BAD_REQUEST);
        }

        // Validate stock when tracking is enabled or branch stock exists
        $productBranch = \App\Models\ProductBranch::where('product_id', $product->id)
            ->where('branch_id', $branchId)
            ->first();

        if ($product->track_stock || $productBranch) {
            if (!$productBranch || $productBranch->stock_quantity < $request->quantity) {
                $available = $productBranch?->stock_quantity ?? 0;
                return MyHelper::responseJSON("Insufficient stock. Available: {$available}", Response::HTTP_BAD_REQUEST);
            }
        }

        // Check if product already in cart
        foreach ($cart['items'] as $item) {
            if ($item['type'] === 'product' && $item['id'] == $request->product_id) {
                return MyHelper::responseJSON('Product already in cart', Response::HTTP_BAD_REQUEST);
            }
        }

        // Get price (retail_price if available, else supply_price)
        $price = $product->retail_price && $product->retail_price > 0 
            ? $product->retail_price 
            : ($product->supply_price ?? 0);

        $cart['items'][] = [
            'type' => 'product',
            'id' => $product->id,
            'name' => $product->name,
            'price' => $price,
            'quantity' => $request->quantity,
        ];

        session(['sales_cart' => $cart]);
        return MyHelper::responseJSON('Product added to cart', Response::HTTP_OK, ['cart' => $cart]);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        $index = $request->index;
        $cart = session('sales_cart', ['items' => []]);

        if (isset($cart['items'][$index])) {
            unset($cart['items'][$index]);
            $cart['items'] = array_values($cart['items']); // Reindex array
            session(['sales_cart' => $cart]);
            return MyHelper::responseJSON('Item removed from cart', Response::HTTP_OK, ['cart' => $cart]);
        }

        return MyHelper::responseJSON('Item not found', Response::HTTP_NOT_FOUND);
    }

    /**
     * Show payment/review page
     */
    public function payment(Request $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $cart = session('sales_cart', [
            'items' => [],
            'client_id' => null,
            'worker_id' => null,
            'tip' => 0,
            'tax' => 0,
            'payment_type' => null,
        ]);

        // Ensure items is an array and filter out null/empty items
        if (!isset($cart['items']) || !is_array($cart['items'])) {
            $cart['items'] = [];
        }
        $cart['items'] = array_values(array_filter($cart['items']));

        if (empty($cart['items'])) {
            return redirect()->route('center_user.sales.cart')->with('error', 'Cart is empty');
        }

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('field.payment');

        // $paymentMethods = PaymentMethod::forBooking()->orWhereJsonContains('types', 'general')->get();
        $paymentMethods = PaymentMethod::query()
            ->where('status', true)
            ->where(function ($q) {
                $q->whereJsonContains('types', 'booking')
                ->orWhereJsonContains('types', 'general');
            })
            ->get();
        
        // Get selected customer if exists
        $selectedCustomer = null;
        $branchId = null;
        if (!empty($cart['client_id'])) {
            $selectedCustomer = User::with(['media'])->find($cart['client_id']);
            if ($selectedCustomer && $selectedCustomer->branch_id) {
                $branchId = $selectedCustomer->branch_id;
            }
        }
        
        // If no customer branch, use logged-in user's branch
        if (!$branchId) {
            $branchId = auth('center_user')->user()->branch_id ?? null;
        }
        
        // Filter workers by branch
        $workers = Worker::when($branchId, function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->select('id', 'name', 'phone', 'is_center_user')->get();

        // Extract employees/workers from cart items (bookings)
        $cartEmployees = [];
        if (!empty($cart['items']) && is_array($cart['items'])) {
            foreach ($cart['items'] as $item) {
                if (isset($item['type']) && $item['type'] === 'service') {
                    if (!empty($item['services']) && is_array($item['services'])) {
                        foreach ($item['services'] as $svc) {
                            $workerName = $svc['worker_name'] ?? null;
                            if ($workerName && !in_array($workerName, $cartEmployees, true)) {
                                $cartEmployees[] = $workerName;
                            }
                        }
                    } elseif (isset($item['worker_name'])) {
                        if (!in_array($item['worker_name'], $cartEmployees, true)) {
                            $cartEmployees[] = $item['worker_name'];
                        }
                    }
                }
            }
        }

        $centerUser = auth('center_user')->user();
        $view = 'CenterUser.SubViews.' . $this->model . '.payment';
        return view($view, compact('cart', 'workers', 'cartEmployees', 'paymentMethods', 'selectedCustomer', 'title', 'menu', 'menu_link', 'centerUser'));
    }

    /**
     * Process payment and create sale
     */
    public function processPayment(Request $request)
    {
        $can = 'CREATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'tip' => 'nullable|numeric|min:0|max:200',
            'tax' => 'nullable|numeric|min:0',
        ]);

        $cart = session('sales_cart', ['items' => []]);

        if (empty($cart['items'])) {
            return MyHelper::responseJSON('Cart is empty', Response::HTTP_BAD_REQUEST);
        }

        // Update cart with payment info
        $cart['worker_id'] = $request->worker_id;
        $cart['tip'] = $request->tip ?? 0;
        $cart['tax'] = $request->tax ?? 0;

        try {
            $sale = $this->salesService->processSale($cart);
            Log::info('sale', [$sale]);

            // Clear cart
            session()->forget('sales_cart');

            $autoPrint = app(InvoiceSettingsService::class)->first()->auto_print_sale;

            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, [
                'redirect_url' => route('center_user.sales.index'),
                'sale_id' => $sale->id,
                'print_url' => $autoPrint ? route('center_user.sales.print', ['id' => $sale->id]) : null,
            ]);
        } catch (\Exception $e) {
            return MyHelper::responseJSON($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            
        }
    }

    /**
     * Show add-tip form for an existing sale (after booking).
     */
    public function tip($id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')
            && !auth('center_user')->user()->can('CREATE_' . Str::upper($this->plural), 'center_api')
            && !auth('center_user')->user()->can('SHOW_' . Str::upper($this->plural), 'center_api')) {
            return abort(403);
        }

        $sale = Sale::with([
            'client.media',
            'branch.translation',
            'worker',
            'bookings.details.service.translation',
            'bookings.details.worker',
            'saleItems',
        ])->findOrFail($id);

        if (!$this->salesService->saleCanAddTip($sale)) {
            return redirect()
                ->route($this->indexRoute)
                ->with('error', __('field.tip_already_added'));
        }

        $branchId = $sale->branch_id ?? auth('center_user')->user()->branch_id ?? null;
        $workers = Worker::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'is_center_user']);

        $bookingEmployees = [];
        $preferredWorkerId = null;
        foreach ($sale->bookings as $booking) {
            foreach ($booking->details as $detail) {
                $name = $detail->worker?->name;
                if ($name && !in_array($name, $bookingEmployees, true)) {
                    $bookingEmployees[] = $name;
                }
                if (!$preferredWorkerId && $detail->worker_id) {
                    $preferredWorkerId = $detail->worker_id;
                }
            }
        }

        $centerUser = auth('center_user')->user();
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('field.add_tip');

        return view('CenterUser.SubViews.Sale.tip', compact(
            'sale',
            'workers',
            'bookingEmployees',
            'preferredWorkerId',
            'centerUser',
            'title',
            'menu',
            'menu_link'
        ));
    }

    /**
     * Store tip on an existing sale.
     */
    public function storeTip(Request $request, $id)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')
            && !auth('center_user')->user()->can('CREATE_' . Str::upper($this->plural), 'center_api')) {
            return abort(403);
        }

        $request->validate([
            'worker_id' => 'nullable|exists:workers,id',
            'tip' => 'required|numeric|min:0.01|max:200',
        ]);

        $sale = Sale::with(['bookings.details'])->findOrFail($id);

        try {
            $this->salesService->addTipToSale(
                $sale,
                (float) $request->tip,
                $request->filled('worker_id') ? (int) $request->worker_id : null
            );

            return MyHelper::responseJSON(
                __('admin.operation_done_successfully'),
                Response::HTTP_OK,
                route($this->indexRoute)
            );
        } catch (\Exception $e) {
            return MyHelper::responseJSON($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Show sale details
     */
    public function show($id)
    {
        $can = 'SHOW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $sale = Sale::with([
            'worker' => fn ($q) => $q->withTrashed(),
            'client',
            'branch.translation',
            'saleItems',
            'bookings' => function ($q) {
                $q->withTrashed()->with([
                    'details.service.translation',
                    'details.worker' => fn ($wq) => $wq->withTrashed(),
                ]);
            },
            'buyProducts.details.product.translation',
            'buyProducts.sales_worker' => fn ($q) => $q->withTrashed(),
            'buyProducts.worker' => fn ($q) => $q->withTrashed(),
        ])->withTrashed()->findOrFail($id);

        // Load relationships based on item type (packages / wallets / morph fallbacks)
        foreach ($sale->saleItems as $saleItem) {
            if ($saleItem->item_type === 'booking' && $saleItem->itemable) {
                $saleItem->itemable->load([
                    'details.service.translation',
                    'details.worker' => fn ($wq) => $wq->withTrashed(),
                ]);
            } elseif ($saleItem->item_type === 'buy_product' && $saleItem->itemable) {
                $saleItem->itemable->load(['details.product.translation']);
            } elseif ($saleItem->item_type === 'user_package' && $saleItem->itemable) {
                $saleItem->itemable->load(['package']);
            }
        }

        $currency = trim((string) get_currency());
        $paymentMethods = [];
        $bookingNotes = [];
        $bookingEmployees = [];
        $bookingRows = [];

        foreach ($sale->bookings ?? [] as $booking) {
            if (!empty($booking->notes)) {
                $bookingNotes[] = trim((string) $booking->notes);
            }
            if (($booking->payment_type ?? null) === 'multiple' && is_array($booking->payment_methods ?? null)) {
                foreach ($booking->payment_methods as $method) {
                    $name = is_array($method) ? ($method['method'] ?? null) : null;
                    if ($name && !in_array($name, $paymentMethods, true)) {
                        $paymentMethods[] = (string) $name;
                    }
                }
            } elseif (!empty($booking->payment_type) && !in_array($booking->payment_type, $paymentMethods, true)) {
                $paymentMethods[] = (string) $booking->payment_type;
            }

            foreach ($booking->details ?? [] as $detail) {
                $emp = $detail->worker?->name ?? null;
                if ($emp && !in_array($emp, $bookingEmployees, true)) {
                    $bookingEmployees[] = $emp;
                }
                $statusKey = (string) ($detail->status ?? ($booking->deleted_at ? 'cancelled' : 'confirmed'));
                $tipAmount = (float) ($detail->tip ?? 0);
                $from = (string) ($detail->from_time ?? '');
                $to = (string) ($detail->to_time ?? '');
                $bookingRows[] = [
                    'service' => (string) ($detail->service?->translation?->name ?? $detail->service?->name ?? '-'),
                    'worker' => (string) ($emp ?: '-'),
                    'date' => (string) ($detail->_date ?? $booking->booking_date ?? '-'),
                    'time' => ($from !== '' || $to !== '') ? trim($from . ' - ' . $to) : '-',
                    'price' => number_format((float) ($detail->price ?? 0), 2) . ' ' . $currency,
                    'tip' => $tipAmount > 0 ? number_format($tipAmount, 2) . ' ' . $currency : '-',
                    'status' => (string) __('field.booking_status_' . $statusKey),
                    'is_free' => (bool) ($detail->is_free ?? false),
                    'payment' => (string) ($booking->payment_type ?? '-'),
                ];
            }
        }

        foreach ($sale->buyProducts ?? [] as $buyProduct) {
            if (!empty($buyProduct->payment_type) && !in_array($buyProduct->payment_type, $paymentMethods, true)) {
                $paymentMethods[] = (string) $buyProduct->payment_type;
            }
        }

        $workerName = (string) ($sale->worker?->name ?? '');
        if ($workerName === '' && !empty($bookingEmployees)) {
            $workerName = $bookingEmployees[0];
        }
        if ($workerName === '') {
            $workerName = '-';
        }

        $tipTotal = (float) ($sale->tip ?? 0);
        if ($tipTotal <= 0) {
            foreach ($sale->bookings ?? [] as $booking) {
                foreach ($booking->details ?? [] as $detail) {
                    $tipTotal += (float) ($detail->tip ?? 0);
                }
            }
        }

        $statusLabel = '-';
        $firstBooking = optional($sale->bookings)->first();
        if ($firstBooking) {
            $firstDetail = optional($firstBooking->details)->first();
            $status = $firstDetail?->status ?? 'confirmed';
            $source = $firstDetail?->booking_source ?? 'inside_booking';
            if ($source !== 'outside_booking') {
                $statusLabel = (string) __('field.booking_status_' . ($firstBooking->deleted_at ? 'cancelled' : 'confirmed'));
            } else {
                $statusLabel = (string) __('field.booking_status_' . $status);
            }
        }

        $saleDisplay = [
            'branch' => (string) ($sale->branch?->translation?->name ?? $sale->branch?->name ?? '-'),
            'phone' => (string) ($sale->client?->full_phone ?? $sale->client?->phone ?? '-'),
            'payment' => empty($paymentMethods) ? '-' : implode(', ', $paymentMethods),
            'worker' => $workerName,
            'booking_employees' => $bookingEmployees,
            'tip_total' => $tipTotal,
            'tip_label' => $tipTotal > 0 && $workerName !== '-'
                ? (__('field.tip') . ' (' . $workerName . ')')
                : __('field.tip'),
            'status_label' => $statusLabel,
            'notes' => implode("\n", array_values(array_unique(array_filter($bookingNotes)))),
            'booking_rows' => $bookingRows,
            'currency' => $currency,
        ];

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('general.show') . ' ' . __('locale.' . $this->plural);
        Log::info('sale show', [$sale]);
        $view = 'CenterUser.SubViews.' . $this->model . '.show';
        return view($view, compact('sale', 'saleDisplay', 'title', 'menu', 'menu_link'));
    }

    /**
     * Print sale receipt
     */
    public function print($id, InvoiceSettingsService $invoiceSettingsService)
    {
        $sale = Sale::with([
            'worker',
            'client',
            'branch',
            'saleItems'
        ])->findOrFail($id);
        
        // Load relationships based on item type
        foreach ($sale->saleItems as $saleItem) {
            if ($saleItem->item_type === 'booking' && $saleItem->itemable) {
                $saleItem->itemable->load(['details.service.translation']);
            } elseif ($saleItem->item_type === 'buy_product' && $saleItem->itemable) {
                $saleItem->itemable->load(['details.product.translation']);
            }
        }

        $options = [
            'format' => [80, 200],
            'orientation' => 'portrait',
            'margin-top' => 10,
            'margin-bottom' => 10,
            'margin-left' => 10,
            'margin-right' => 10,
        ];

        $invoice_info = Setting::where('key', SettingEnum::invoice_info->value)->first()->value ?? '';
        $center = $this->resolveActiveCenter();
        $invoiceSettings = $invoiceSettingsService->first();
        $template = (string) view('CenterUser.SubViews.Report.template.invoice_info', compact(
            'invoice_info',
            'center',
            'invoiceSettings'
        ));

        $view = 'CenterUser.SubViews.' . $this->model . '.print';
        $pdf = Pdf::loadView($view, compact('sale', 'template'), [], $options);
        return $pdf->stream('sale_' . $id . '.pdf');
    }

    private function resolveActiveCenter(): ?Center
    {
        $host = request()->getHost();

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return Center::with('media')->where('domain', 'center35')->first();
        }

        $domain = session('active_center_domain');

        if ($domain) {
            return Center::with('media')->where('domain', $domain)->first();
        }

        $parts     = explode('.', $host);
        $subdomain = count($parts) > 2 && $parts[0] !== 'www' ? $parts[0] : null;

        return $subdomain ? Center::with('media')->where('domain', $subdomain)->first() : null;
    }

    public function requestOtp(Request $request, SaleOtpService $saleOtpService)
    {
        $canDelete = auth('center_user')->user()->can('DELETE_SALES', 'center_api');
        $canUpdate = auth('center_user')->user()->can('UPDATE_SALES', 'center_api')
            || auth('center_user')->user()->can('SHOW_SALES', 'center_api');

        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'action' => 'required|in:delete,edit',
            'reason' => 'required|string|min:3|max:200',
            'new_date' => 'nullable|date|required_if:action,edit',
        ]);

        if ($request->action === 'delete' && !$canDelete) {
            return abort(403);
        }
        if ($request->action === 'edit' && !$canUpdate) {
            return abort(403);
        }

        $sale = Sale::withTrashed()->findOrFail($request->sale_id);
        $result = $saleOtpService->requestOtp(
            $sale,
            $request->action,
            $request->reason,
            $request->new_date
        );

        if (!$result['success']) {
            return MyHelper::responseJSON($result['message'], Response::HTTP_BAD_REQUEST);
        }

        return MyHelper::responseJSON($result['message'], Response::HTTP_OK, [
            'masked_phones' => $result['masked_phones'] ?? [],
        ]);
    }

    public function verifyOtp(Request $request, SaleOtpService $saleOtpService)
{
    // new_date is optional here — in the normal flow it is stored inside the
    // OTP pending record (session), not sent again by the client.
    $request->validate([
        'sale_id'  => 'required|exists:sales,id',
        'action'   => 'required|in:delete,edit',
        'code'     => 'required|string|min:4|max:8',
        'new_date' => 'nullable|date',
    ]);

    $canDelete = auth('center_user')->user()->can('DELETE_SALES', 'center_api');
    $canUpdate = auth('center_user')->user()->can('UPDATE_SALES', 'center_api')
        || auth('center_user')->user()->can('SHOW_SALES', 'center_api');

    if ($request->action === 'delete' && !$canDelete) {
        return abort(403);
    }
    if ($request->action === 'edit' && !$canUpdate) {
        return abort(403);
    }

    // =====================================================================
    // TEST-ONLY OTP BYPASS
    // Falls back gracefully so you don't need to send new_date from tests.
    // Order: session pending → request input → today.
    // =====================================================================
    if (config('app.sale_otp_bypass', false)) {
        Log::warning('SALE OTP BYPASS USED — OTP verification skipped', [
            'sale_id' => $request->sale_id,
            'action'  => $request->action,
            'user_id' => auth('center_user')->id(),
            'ip'      => $request->ip(),
        ]);

        // ✅ FIX: if requestOtp already ran, reuse its pending record.
        $sessionPending = session(\App\Services\SaleOtpService::SESSION_KEY) ?: [];

        $verify = ['success' => true, 'pending' => [
            'reason'   => $sessionPending['reason'] ?? 'test-bypass',
            'new_date' => $sessionPending['new_date']
                       ?? $request->input('new_date')
                       ?? now()->toDateString(),   // ✅ FIX: final fallback
        ]];
    } else {
        $verify = $saleOtpService->verifyPending(
            $request->code,
            (int) $request->sale_id,
            $request->action
        );
    }

    if (!$verify['success']) {
        return MyHelper::responseJSON($verify['message'], Response::HTTP_BAD_REQUEST);
    }

    $pending = $verify['pending'];
    $sale    = Sale::withTrashed()->findOrFail($request->sale_id);

    try {

        // =================================================================
        // DELETE BRANCH
        // =================================================================
      // =================================================================
// DELETE BRANCH
// =================================================================
if ($request->action === 'delete') {

    // BuyProduct uses SoftDeletes → load withTrashed + details.
    // Booking uses SoftDeletes → load withTrashed + details + user.
    // BookingDetail has NO SoftDeletes → do not call withTrashed on it.
    // client + saleItems loaded so we can include them in the notification
    // before the sale is forceDeleted.
    $sale->load([
        'client',
        'saleItems',
        'branch',
        'buyProducts' => function ($q) {
            $q->withTrashed()->with('details');
        },
        'bookings' => function ($q) {
            $q->withTrashed()->with(['details', 'user']);
        },
    ]);

    // Capture everything we need BEFORE forceDelete.
    $deletedSaleId    = (int) $sale->id;
    $deletedTotal     = (float) ($sale->total ?? 0);
    $deletedCustomer  = trim(
        (string) ($sale->client?->first_name ?? '') . ' ' .
        (string) ($sale->client?->last_name ?? '')
    );
    if ($deletedCustomer === '') {
        $deletedCustomer = (string) ($sale->client?->name ?? '');
    }
    if ($deletedCustomer === '') {
        $deletedCustomer = (string) ($sale->client?->phone ?? 'Unknown customer');
    }
    $deletedItemsCount = (int) $sale->saleItems->count();
    $deletedBookings   = $sale->bookings->pluck('id')->values()->all();
    $deletedBuyProds   = $sale->buyProducts->pluck('id')->values()->all();

    $branchId        = (int) ($sale->branch_id ?? 0);
    $movementService = app(\App\Services\InventoryMovementService::class);

    \Illuminate\Support\Facades\DB::transaction(function () use ($sale, $branchId, $movementService) {

        // ---- 1. Restore product stock ----
        if ($branchId) {
            foreach ($sale->buyProducts as $buyProduct) {
                $qtyByProduct = $buyProduct->details->groupBy('product_id')->map->count();

                foreach ($qtyByProduct as $productId => $qty) {
                    $productBranch = \App\Models\ProductBranch::where('product_id', $productId)
                        ->where('branch_id', $branchId)
                        ->first();

                    if ($productBranch) {
                        $productBranch->increment('stock_quantity', (int) $qty);
                    }

                    $movementService->record(
                        (int) $productId,
                        $branchId,
                        (int) $qty,
                        \App\Models\InventoryMovement::TYPE_SALE_DELETED,
                        $sale,
                        null,
                        'Sale deleted #' . $sale->id . ' — stock restored'
                    );
                }
            }
        }

        // ---- 2. Cascade-delete buy products + their details ----
        foreach ($sale->buyProducts as $buyProduct) {
            $detailModel = $buyProduct->details()->getRelated();
            $detailUsesSoftDeletes = in_array(
                \Illuminate\Database\Eloquent\SoftDeletes::class,
                class_uses_recursive($detailModel),
                true
            );

            if ($detailUsesSoftDeletes) {
                $buyProduct->details()->withTrashed()->forceDelete();
            } else {
                $buyProduct->details()->delete();
            }

            $buyProduct->forceDelete();
        }

        // ---- 3. Cascade-delete associated bookings ----
        foreach ($sale->bookings as $booking) {

            try {
                app(\App\Services\BookingNotificationService::class)
                    ->notifyStatusChange($booking->fresh(['user', 'details']), 'cancelled');
            } catch (\Throwable $e) {
                Log::warning('Booking cancel notification failed on sale delete', [
                    'sale_id'    => $sale->id,
                    'booking_id' => $booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }

            $booking->details()->delete();   // BookingDetail has no SoftDeletes
            $booking->forceDelete();         // Booking has SoftDeletes
        }

        // ---- 4. Delete the sale (saleItems cascade via FK) ----
        $sale->forceDelete();
    });

    $saleOtpService->clear();

    // ---- 5. Notify the center (OUTSIDE the transaction) ----
    $this->notifySaleDeleted(
        $deletedSaleId,
        $deletedTotal,
        $deletedCustomer,
        $deletedItemsCount,
        $deletedBookings,
        $deletedBuyProds,
        (string) ($pending['reason'] ?? '')
    );

    Log::info('Sale deleted after OTP', [
        'sale_id'              => $deletedSaleId,
        'reason'               => $pending['reason'] ?? null,
        'bookings_deleted'     => $deletedBookings,
        'buy_products_deleted' => $deletedBuyProds,
        'by'                   => auth('center_user')->id(),
    ]);

    return MyHelper::responseJSON(__('admin.done_delete_successfully'), Response::HTTP_OK);
    }

        
            //   pending (from OTP session or bypass) → request → error
            $newDateRaw = $pending['new_date'] ?? $request->input('new_date');

            if (empty($newDateRaw)) {
                return MyHelper::responseJSON(
                    __('general.date_required'),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            try {
                $newDate = Carbon::parse($newDateRaw);
            } catch (\Throwable $e) {
                return MyHelper::responseJSON(
                    __('general.invalid_date'),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // ---- Capture state BEFORE the edit so we can diff & notify ----
            $oldDateRaw    = $sale->getRawOriginal('created_at'); // untouched value
            $oldDatePretty = $oldDateRaw ? Carbon::parse($oldDateRaw) : null;
            $oldBookingIds = $sale->bookings()->pluck('id')->values()->all();

            $sale->load(['bookings.details']);
            $sale->created_at = $newDate->copy()->setTimeFrom(Carbon::parse($sale->created_at));
            $sale->save();

            foreach ($sale->bookings as $booking) {
                $booking->booking_date = $newDate->toDateString();
                $booking->save();
                foreach ($booking->details as $detail) {
                    $detail->_date = $newDate->toDateString();
                    $detail->save();
                }
            }

            $saleOtpService->clear();

            // ---- Notify the center (after successful save) ----
            $this->notifySaleDateEdited(
                (int) $sale->id,
                $oldDatePretty ? $oldDatePretty->format('Y-m-d') : null,
                $newDate->toDateString(),
                (int) $sale->bookings->count(),
                $oldBookingIds,
                (string) ($pending['reason'] ?? '')
            );

            Log::info('Sale date updated after OTP', [
                'sale_id'        => $request->sale_id,
                'old_date'       => $oldDatePretty?->format('Y-m-d'),
                'new_date'       => $newDate->toDateString(),
                'reason'         => $pending['reason'] ?? null,
                'bookings_count' => $sale->bookings->count(),
                'by'             => auth('center_user')->id(),
            ]);

            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_OK, [
                'created_at' => $sale->created_at
                    ? Carbon::parse($sale->created_at)->format('Y-m-d H:i')
                    : null,
            ]);

        } catch (\Throwable $e) {
            Log::error('Sale OTP action failed', [
                'sale_id' => $request->sale_id,
                'action'  => $request->action,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


/**
 * Notify the active center that a sale was deleted (via OTP flow).
 *
 * Called AFTER the tenant transaction commits and AFTER $sale->forceDelete(),
 * so it works on captured primitives, not on the (now-gone) Eloquent model.
 *
 * Best-effort: the NotifiesCenter trait swallows + logs every error internally,
 * so a push/notification failure can never affect the OTP response.
 */
    private function notifySaleDeleted(
        int $saleId,
        float $total,
        string $customerName,
        int $itemsCount,
        array $bookingIds,
        array $buyProductIds,
        string $reason = ''
    ): void {
        try {
            $customerName = $customerName !== '' ? $customerName : 'Unknown customer';
            $priceFormatted = number_format($total, 2);

            $textEn = sprintf(
                'Sale #%d (customer: %s, %d item(s), total %s) was deleted.',
                $saleId,
                $customerName,
                $itemsCount,
                $priceFormatted
            );
            if ($reason !== '') {
                $textEn .= ' Reason: ' . $reason;
            }

            $textAr = sprintf(
                'تم حذف عملية البيع رقم %d (العميل: %s، عدد العناصر: %d، الإجمالي: %s).',
                $saleId,
                $customerName,
                $itemsCount,
                $priceFormatted
            );
            if ($reason !== '') {
                $textAr .= ' السبب: ' . $reason;
            }

            $this->notifyCenter(
                'sale',
                [
                    'en' => ['title' => 'Sale Deleted',     'text' => $textEn],
                    'ar' => ['title' => 'تم حذف عملية بيع', 'text' => $textAr],
                ],
                [
                    'action'        => 'sale_deleted',
                    'sale_id'       => (string) $saleId,
                    'total'         => $priceFormatted,
                    'items_count'   => (string) $itemsCount,
                    'bookings'      => implode(',', array_map('strval', $bookingIds)),
                    'buy_products'  => implode(',', array_map('strval', $buyProductIds)),
                    'reason'        => $reason,
                ]
            );
        } catch (\Throwable $e) {
            // The trait already catches its own errors — this is a final safety net.
            Log::warning('notifySaleDeleted failed', [
                'sale_id' => $saleId,
                'error'   => $e->getMessage(),
            ]);
        }
    }
    /**
 * Notify the active center that a sale's date was edited (via OTP flow).
 *
 * Called AFTER the edit is persisted. Uses captured primitives so the
 * notification doesn't depend on the sale still being mutable.
 *
 * Best-effort: the NotifiesCenter trait swallows + logs every error.
 */
private function notifySaleDateEdited(
    int $saleId,
    ?string $oldDate,
    string $newDate,
    int $bookingsCount,
    array $bookingIds,
    string $reason = ''
): void {
    try {
        $oldDisplay = $oldDate ?: '—';

        $textEn = sprintf(
            'Sale #%d date changed from %s to %s%s.',
            $saleId,
            $oldDisplay,
            $newDate,
            $bookingsCount > 0 ? ' (' . $bookingsCount . ' booking(s) rescheduled)' : ''
        );
        if ($reason !== '') {
            $textEn .= ' Reason: ' . $reason;
        }

        $textAr = sprintf(
            'تم تعديل تاريخ عملية البيع رقم %d من %s إلى %s%s.',
            $saleId,
            $oldDisplay,
            $newDate,
            $bookingsCount > 0 ? ' (إعادة جدولة ' . $bookingsCount . ' حجز)' : ''
        );
        if ($reason !== '') {
            $textAr .= ' السبب: ' . $reason;
        }

        $this->notifyCenter(
            'sale',
            [
                'en' => ['title' => 'Sale Date Edited',     'text' => $textEn],
                'ar' => ['title' => 'تم تعديل تاريخ عملية بيع', 'text' => $textAr],
            ],
            [
                'action'         => 'sale_date_edited',
                'sale_id'        => (string) $saleId,
                'old_date'       => (string) ($oldDate ?? ''),
                'new_date'       => $newDate,
                'bookings_count' => (string) $bookingsCount,
                'bookings'       => implode(',', array_map('strval', $bookingIds)),
                'reason'         => $reason,
            ]
        );
    } catch (\Throwable $e) {
        Log::warning('notifySaleDateEdited failed', [
            'sale_id' => $saleId,
            'error'   => $e->getMessage(),
        ]);
    }
}
}

