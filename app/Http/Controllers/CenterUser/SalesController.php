<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\SalesDataTable;
use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Center;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Sale;
use App\Models\User;
use App\Models\Worker;
use App\Services\InvoiceSettingsService;
use App\Services\SalesService;
use App\Services\SaleOtpService;
use App\Services\CustomerSearchService;
use App\Traits\CategoryTreeTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SalesController extends Controller
{
    use CategoryTreeTrait;

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
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
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
        
        // Use service to get all dependencies
        $data = $this->salesService->getCartData($cart, $centerUser);
        
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

        $categoriesJson = $this->getFormattedCategories();

        $view = 'CenterUser.SubViews.' . $this->model . '.cart';
        return view($view, compact('services', 'products', 'workers', 'discounts', 'packages', 'paymentMethods', 'productPaymentMethods', 'walletPaymentMethods', 'wallets', 'selectedUser', 'cart', 'title', 'menu', 'menu_link', 'categoriesJson', 'centerUser'));
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
        $branchId = auth('center_user')->user()->branch_id ?? Branch::first()->id;

        // Validate stock
        if ($product->track_stock) {
            $productBranch = \App\Models\ProductBranch::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->first();

            if (!$productBranch || $productBranch->stock_quantity < $request->quantity) {
                return MyHelper::responseJSON('Insufficient stock', Response::HTTP_BAD_REQUEST);
            }
        }

        $cart = session('sales_cart', ['items' => []]);

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

        $paymentMethods = \App\Models\PaymentMethod::forBooking()->orWhereJsonContains('types', 'general')->get();
        
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

            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('center_user.sales.index'));
        } catch (\Exception $e) {
            return MyHelper::responseJSON($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            
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

        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);
        $title = __('general.show') . ' ' . __('locale.' . $this->plural);
        Log::info('sale show', [$sale]);
        $view = 'CenterUser.SubViews.' . $this->model . '.show';
        return view($view, compact('sale', 'title', 'menu', 'menu_link'));
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
            return Center::with('media')->where('domain', 'center')->first();
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
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'action' => 'required|in:delete,edit',
            'code' => 'required|string|min:4|max:8',
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

        $verify = $saleOtpService->verifyPending($request->code, (int) $request->sale_id, $request->action);
        if (!$verify['success']) {
            return MyHelper::responseJSON($verify['message'], Response::HTTP_BAD_REQUEST);
        }

        $pending = $verify['pending'];
        $sale = Sale::withTrashed()->findOrFail($request->sale_id);

        try {
            if ($request->action === 'delete') {
                $sale->forceDelete();
                $saleOtpService->clear();
                Log::info('Sale deleted after OTP', [
                    'sale_id' => $request->sale_id,
                    'reason' => $pending['reason'] ?? null,
                    'by' => auth('center_user')->id(),
                ]);

                return MyHelper::responseJSON(__('admin.done_delete_successfully'), Response::HTTP_OK);
            }

            $newDate = Carbon::parse($pending['new_date']);
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
            Log::info('Sale date updated after OTP', [
                'sale_id' => $request->sale_id,
                'new_date' => $pending['new_date'] ?? null,
                'reason' => $pending['reason'] ?? null,
                'by' => auth('center_user')->id(),
            ]);

            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_OK, [
                'created_at' => $sale->created_at?->format('Y-m-d H:i'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Sale OTP action failed', [
                'sale_id' => $request->sale_id,
                'action' => $request->action,
                'error' => $e->getMessage(),
            ]);

            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

