<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\SalesDataTable;
use App\Enums\SettingEnum;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Sale;
use App\Models\User;
use App\Models\Worker;
use App\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class SalesController extends Controller
{
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

        // Get services and products
        $services = Service::with(['translation'])->get();
        $products = Product::with(['translation', 'branches'])->get();
        $discounts = \App\Models\Discount::all();
        $paymentMethods = \App\Models\PaymentMethod::forBooking()->orWhereJsonContains('types', 'general')->get();
        $productPaymentMethods = \App\Models\PaymentMethod::forProduct()->orWhereJsonContains('types', 'general')->get();
        $walletPaymentMethods = \App\Models\PaymentMethod::forWallet()->get();
        
        
        $wallets = \App\Models\Wallet::with(['created_by_user', 'users.user'])
             ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->get();
        
        // Get all users (customers) - no branch filtering
        $users = User::with(['media'])->get();

        // Save cart if posted (AJAX request)
        if ($request->isMethod('post') && $request->has('cart')) {
            $cart = session('sales_cart', [
                'items' => [],
                'client_id' => null,
                'worker_id' => null,
                'tip' => 0,
                'tax' => 0,
                'payment_type' => null,
            ]);
            // Filter out null/empty items and re-index
            $cart['items'] = array_values(array_filter($request->cart));
            if ($request->has('client_id')) {
                $cart['client_id'] = $request->client_id;
            }
            session(['sales_cart' => $cart]);
            return MyHelper::responseJSON('Cart saved', Response::HTTP_OK, ['items' => $cart['items']]);
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
        
        // Filter workers by selected customer's branch
        $branchId = null;
        if (!empty($cart['client_id'])) {
            $selectedCustomer = User::find($cart['client_id']);
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
        })->get();

        $view = 'CenterUser.SubViews.' . $this->model . '.cart';
        return view($view, compact('services', 'products', 'workers', 'discounts', 'paymentMethods', 'productPaymentMethods', 'walletPaymentMethods', 'wallets', 'users', 'cart', 'title', 'menu', 'menu_link'));
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
        })->get();

        $view = 'CenterUser.SubViews.' . $this->model . '.payment';
        return view($view, compact('cart', 'workers', 'paymentMethods', 'selectedCustomer', 'title', 'menu', 'menu_link'));
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

        $view = 'CenterUser.SubViews.' . $this->model . '.show';
        return view($view, compact('sale', 'title', 'menu', 'menu_link'));
    }

    /**
     * Print sale receipt
     */
    public function print($id)
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
        $template = (string)view('CenterUser.SubViews.Report.template.invoice_info', compact('invoice_info'));

        $view = 'CenterUser.SubViews.' . $this->model . '.print';
        $pdf = Pdf::loadView($view, compact('sale', 'template'), [], $options);
        return $pdf->stream('sale_' . $id . '.pdf');
    }
}

