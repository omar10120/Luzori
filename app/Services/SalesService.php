<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Branch;
use App\Models\BuyProduct;
use App\Models\BuyProductDetail;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\User;
use App\Models\Discount;
use App\Models\UserUsedDiscount;
use App\Models\UserUsedWallet;
use App\Models\UserUsedCard;
use App\Models\Membership;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\UserPackage;
use App\Models\UserUsedPackage;
use App\Models\PackageServicePaid;
use App\Models\PackageServiceFree;
use App\Services\SMSGatewayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\Worker;
use App\Models\InventoryMovement;



class SalesService
{
    /**
     * Get lean data needed for the initial cart view.
     * Heavy wallet/package tables are loaded on-demand via AJAX.
     */
    public function getCartData($cart, $centerUser)
    {
        $services = Service::with('translation')
            ->orderBy('id')
            ->get(['id', 'category_id', 'price', 'has_commission']);

        $products = Product::with('translation')
            ->orderBy('id')
            ->get(['id', 'supply_price', 'retail_price']);

        $discounts = Discount::query()
            ->select('id', 'code', 'type', 'amount', 'start_at', 'end_at')
            ->orderBy('id')
            ->get();

        // Packages list for selects only — no users / nested services on first paint
        $packages = Package::with('translation')
            ->orderBy('id')
            ->get(['id', 'price']);

        $paymentMethodsAll = PaymentMethod::query()
            ->select('id', 'name', 'types')
            ->get();

        $paymentMethods = $paymentMethodsAll->filter(function ($m) {
            $types = $m->types ?? [];
            return in_array(PaymentMethod::TYPE_BOOKING, $types, true)
                || in_array(PaymentMethod::TYPE_GENERAL, $types, true);
        })->values();

        $productPaymentMethods = $paymentMethodsAll->filter(function ($m) {
            $types = $m->types ?? [];
            return in_array(PaymentMethod::TYPE_PRODUCT, $types, true)
                || in_array(PaymentMethod::TYPE_GENERAL, $types, true);
        })->values();

        $walletPaymentMethods = $paymentMethodsAll->filter(function ($m) {
            $types = $m->types ?? [];
            return in_array(PaymentMethod::TYPE_WALLET, $types, true)
                || in_array(PaymentMethod::TYPE_GENERAL, $types, true);
        })->values();

        $selectedId = !empty($cart['client_id']) ? (int) $cart['client_id'] : null;
        $selectedUser = $selectedId
            ? User::query()
                ->select('id', 'first_name', 'last_name', 'email', 'country_code', 'phone', 'branch_id')
                ->with('media')
                ->find($selectedId)
            : null;

        $branchId = $selectedUser?->branch_id ?? $centerUser->branch_id ?? null;

        $workers = Worker::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'is_center_user']);

        return [
            'services' => $services,
            'products' => $products,
            'discounts' => $discounts,
            'packages' => $packages,
            'paymentMethods' => $paymentMethods,
            'productPaymentMethods' => $productPaymentMethods,
            'walletPaymentMethods' => $walletPaymentMethods,
            'wallets' => collect(), // lazy-loaded
            'selectedUser' => $selectedUser,
            'workers' => $workers,
            'branchId' => $branchId,
        ];
    }

    /**
     * Coupons table payload for cart wallet tab (lazy).
     */
    public function getCartWallets($centerUser)
    {
        return Wallet::with([
            'created_by_user:id,name',
            'users' => function ($q) {
                $q->select('id', 'wallet_id', 'user_id')
                    ->with(['user:id,first_name,last_name']);
            },
        ])
            ->whereNull('deleted_at')
            ->forCenterUserBranch($centerUser)
            ->orderByDesc('id')
            ->get(['id', 'code', 'amount', 'invoiced_amount', 'used', 'start_at', 'end_at', 'created_by']);
    }

    /**
     * Packages table payload for cart package tab (lazy).
     */
    public function getCartPackages()
    {
        return Package::with([
            'translation',
            'packageServicePaid.service.translation',
            'packageServiceFree.service.translation',
        ])
            ->withCount('usersPackages')
            ->orderByDesc('id')
            ->get(['id', 'price', 'created_by']);
    }

    /**
     * Process cart and create sale with all related records
     */
    public function processSale($cartData, $overrides = [])
    {
        DB::beginTransaction();
        try {
            // Validate cart data
            if (empty($cartData['items'])) {
                throw new \Exception('Cart is empty');
            }

            // Calculate totals
            $subtotal = $this->calculateSubtotal($cartData['items']);
            $tax = $cartData['tax'] ?? 0;
            $tip = $cartData['tip'] ?? 0;
            $total = $subtotal + $tax + $tip;

            // Get branch with override support
            $branchId = $this->resolveSaleBranchId($cartData, $overrides);
            
            if (!$branchId) {
                throw new \Exception('No branch found for this sale');
            }

            // Create Sale record
            $sale = Sale::create([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'tip' => $tip,
                'total' => $total,
                'worker_id' => $cartData['worker_id'] ?? null,
                'client_id' => $cartData['client_id'] ?? null,
                'branch_id' => $branchId,
                'created_by' => $overrides['created_by'] ?? (auth('center_user')->id() ?? auth('center_api')->id()),
            ]);

            // Separate items by type
            $serviceItems = [];
            $productItems = [];
            $walletItems = [];
            $userWalletItems = [];

            foreach ($cartData['items'] as $item) {
                if ($item['type'] === 'service') {
                    $serviceItems[] = $item;
                } elseif ($item['type'] === 'product') {
                    // Validate stock before processing
                    $this->validateProductStock($item['id'], $item['quantity'], $branchId);
                    $productItems[] = $item;
                } elseif ($item['type'] === 'wallet') {
                    $walletItems[] = $item;
                } elseif ($item['type'] === 'user_wallet') {
                    $userWalletItems[] = $item;
                }
            }

            // Process service items (one booking per item map; each item can have multiple services and packages)
            foreach ($serviceItems as $item) {
                // Determine payment type (wallet/membership override payment_type)
                $paymentType = !empty($item['payment_type']) ? $item['payment_type'] : null;
                if (!empty($item['user_package_ids']) && is_array($item['user_package_ids'])) {
                    $packagePaymentType = UserPackage::query()
                        ->whereIn('id', $item['user_package_ids'])
                        ->whereNotNull('package_type')
                        ->value('package_type');

                    // Package usage should force package-type payment instead of stale manual selection.
                    $paymentType = $packagePaymentType ?: ($paymentType ?: 'package');
                }
                if (!empty($item['wallet_id'])) {
                    $paymentType = 'wallet';
                } elseif (!empty($item['membership_id'])) {
                    $paymentType = $item['payment_type'];
                }
                $is_free = $item['is_free'] ?? null;
                
                // --- 1. Process Services ---
                if (!empty($item['services']) || (!isset($item['packages']) && isset($item['id']))) {
                    $bookingSubtotal = 0;
                    if (!empty($item['services']) && is_array($item['services'])) {
                        foreach ($item['services'] as $svc) {
                            $bookingSubtotal += (float) ($svc['price'] ?? 0);
                        }
                    } else {
                        $bookingSubtotal = (float) ($item['price'] ?? 0);
                    }
                    
                    $bookingTip = $tip; // Take current tip to pass by reference
                    $booking = $this->createBookingFromCartItem($item, $sale->id, $branchId, $paymentType, $bookingSubtotal, $is_free, $bookingTip, $cartData['worker_id'] ?? null, $cartData['client_id'] ?? null, $overrides['created_by'] ?? null);
                    $tip = $bookingTip; // Update tip after processing
                    
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'item_type' => 'booking',
                        'itemable_id' => $booking->id,
                        'itemable_type' => 'App\Models\Booking',
                        'quantity' => 1,
                        'price' => $bookingSubtotal,
                        'subtotal' => $bookingSubtotal,
                    ]);
                }

                // --- 2. Process Packages ---
                if (!empty($item['packages']) && is_array($item['packages'])) {
                    $user = null;
                    if (!empty($item['client_mobile'])) {
                        $user = User::where('phone', $item['client_mobile'])->first();
                    }
                    $userId = $user ? $user->id : ($cartData['client_id'] ?? null);

                    foreach ($item['packages'] as $pkg) {
                        $userPackage = \App\Models\UserPackage::create([
                            'user_id' => $userId,
                            'package_id' => $pkg['id'],
                            'price' => $pkg['price'] ?? 0,
                            'status' => 'active',
                        ]);

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'item_type' => 'user_package',
                            'itemable_id' => $userPackage->id,
                            'itemable_type' => 'App\Models\UserPackage',
                            'quantity' => 1,
                            'price' => $pkg['price'] ?? 0,
                            'subtotal' => $pkg['price'] ?? 0,
                        ]);
                    }
                }
            }

            // Process product items (create one BuyProduct for all products)
            if (!empty($productItems)) {
                // Use payment_type from first product item (selected in product form)
                // All products in the same buy_product should have the same payment_type
                $paymentType = $productItems[0]['payment_type'] ?? null;
                $buyProduct = $this->createBuyProductFromCartItems($productItems, $sale->id, $paymentType);
                
                // Create SaleItem for BuyProduct
                $productSubtotal = 0;
                foreach ($productItems as $item) {
                    $productSubtotal += $item['price'] * $item['quantity'];
                }
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_type' => 'buy_product',
                    'itemable_id' => $buyProduct->id,
                    'itemable_type' => 'App\Models\BuyProduct',
                    'quantity' => count($productItems),
                    'price' => $productSubtotal / count($productItems), // Average price
                    'subtotal' => $productSubtotal,
                ]);

                // Update stock for all products
                foreach ($productItems as $item) {
                    $this->updateProductStock(
                        (int) $item['id'],
                        (int) ($item['quantity'] ?? 1),
                        $branchId,
                        $sale
                    );
                }
            }

            // Process wallet items (create wallet for each)
            foreach ($walletItems as $item) {
                $wallet = $this->createWalletFromCartItem($item);
                
                // Create SaleItem for Wallet (if needed, or just create wallet)
                // Note: Wallets might not need SaleItem if they're just created
                // But we can link them to the sale if needed
            }

            // Process user_wallet items (assign existing wallet to user)
            foreach ($userWalletItems as $item) {
                $userWallet = $this->createUserWalletFromCartItem($item, $branchId);
                
                // Create SaleItem for UserWallet
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_type' => 'user_wallet',
                    'itemable_id' => $userWallet->id,
                    'itemable_type' => 'App\Models\UserWallet',
                    'quantity' => 1,
                    'price' => $item['invoiced_amount'] ?? ($item['amount'] ?? 0),
                    'subtotal' => $item['invoiced_amount'] ?? ($item['amount'] ?? 0),
                ]);
            }

            DB::commit();

            $sale->load(['client', 'branch.translation']);
            
            try {
                $this->sendSaleConfirmationSMS($sale);
            } catch (\Exception $e) {
                Log::error('SMS sending failed but sale was completed', [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $sale;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function sendSaleConfirmationSMS(Sale $sale): void
    {
        try {
            if (!$sale->client_id) {
                return;
            }

            $client = $sale->client;
            if (!$client || !$client->phone) {
                return;
            }

            $branch = $sale->branch;
            $salonName = $branch && $branch->translation ? $branch->translation->name : '';

            $userName = trim(($client->first_name ?? '') . ' ' . ($client->last_name ?? ''));
            if (empty($userName)) {
                $userName = __('general.walk_in');
            }

            $locale = app()->getLocale();
            
            $template = trans('general.sms_sale_confirmation', [], $locale);

            $fullPhone = ($client->country_code ?? '') . ($client->phone ?? '');
            if (empty($fullPhone)) {
                return;
            }

            $smsGateway = new SMSGatewayService();
            $formattedPhone = $smsGateway->formatPhoneNumber($fullPhone);
            
            $result = $smsGateway->sendSMSWithTemplate(
                $formattedPhone,
                $template,
                [
                    'user_name' => $userName,
                    'salon_name' => $salonName,
                    'bill_number' => $sale->id,
                ],
                $locale
            );

            if (!$result['success']) {
                Log::warning('Failed to send sale confirmation SMS', [
                    'sale_id' => $sale->id,
                    'client_id' => $client->id,
                    'formatted_phone' => $formattedPhone,
                    'result' => $result,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending sale confirmation SMS', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Calculate subtotal from cart items
     */
    private function calculateSubtotal($items)
    {
        $subtotal = 0;
            foreach ($items as $item) {
                if ($item['type'] === 'service') {
                    if (!empty($item['services']) && is_array($item['services'])) {
                        foreach ($item['services'] as $svc) {
                            $subtotal += (float) ($svc['price'] ?? 0);
                        }
                    } else if (empty($item['packages']) && !empty($item['id'])) {
                        $subtotal += (float) ($item['price'] ?? 0);
                    }
                    
                    if (!empty($item['packages']) && is_array($item['packages'])) {
                        foreach ($item['packages'] as $pkg) {
                            $subtotal += (float) ($pkg['price'] ?? 0);
                        }
                    }
                } elseif ($item['type'] === 'product') {
                    $subtotal += $item['price'] * $item['quantity'];
                } elseif ($item['type'] === 'wallet' || $item['type'] === 'user_wallet') {
                    // Include wallet/coupon invoiced amount in subtotal
                    $subtotal += $item['invoiced_amount'] ?? ($item['amount'] ?? 0);
                }
            }
        return $subtotal;
    }

    /**
     * Create Booking from cart item (one booking with one or more services)
     */
    
    private function createBookingFromCartItem($item, $saleId, $branchId, $paymentType = null, $bookingTotal = 0,$is_free, &$tip, $tipWorkerId = null, $clientId = null, $createdBy = null)
    {
        $services = $item['services'] ?? null;
        if (!empty($services) && is_array($services)) {
            $first = $services[0];
            $bookingDate = $first['date'] ?? null;
        } else {
            $bookingDate = $item['date'] ?? null;
        }
        if (empty($bookingDate)) {
            throw new \Exception('Booking date is required');
        }

        $booking = Booking::create([
            'booking_date' => $bookingDate,
            'full_name' => $item['client_name'] ?? 'Walk-in',
            'mobile' => $item['client_mobile'] ?? null,
            'payment_type' => !empty($paymentType) ? $paymentType : (\App\Models\PaymentMethod::forBooking()->first()->name ?? 'service_cash'),
            'payment_methods' => $item['payment_methods'] ?? null,
            'branch_id' => $branchId,
            'user_id' => $clientId,
            'sale_id' => $saleId,
            'created_by' => $createdBy ?? (auth('center_user')->id() ?? auth('center_api')->id()),
        ]);

        // Load selected user packages and their definitions
        $userPackages = [];
        if (!empty($item['user_package_ids']) && is_array($item['user_package_ids'])) {
            $userPackages = UserPackage::whereIn('id', $item['user_package_ids'])
                ->with(['package.packageServicePaid', 'package.packageServiceFree'])
                ->get();
        }

        // Keep track of used slots during this booking creation
        $packageUsageLog = [];

        if (!empty($services) && is_array($services)) {
            foreach ($services as $svc) {
                if (empty($svc['worker_id']) || empty($svc['from_time']) || empty($svc['to_time'])) {
                    throw new \Exception('Worker and time are required for each service');
                }
                $service = Service::find($svc['id'] ?? null);
                if (!$service) {
                    throw new \Exception('Service not found: ' . ($svc['id'] ?? ''));
                }
                $current_tip = 0;
                if ($tip > 0 && ($tipWorkerId == null || $tipWorkerId == $svc['worker_id'])) {
                    $current_tip = $tip;
                    $tip = 0; // Use tip only once
                }

                $coveredByPackage = false;
                $userPackageIdUsed = null;
                $isFreePackageSlot = 0;

                foreach ($userPackages as $up) {
                    // Check paid services first
                    $paidSlot = $up->package->packageServicePaid->where('service_id', $service->id)->first();
                    if ($paidSlot) {
                        $usedCount = UserUsedPackage::where('user_package_id', $up->id)->where('service_id', $service->id)->where('is_free', 0)->count() + ($packageUsageLog[$up->id][$service->id][0] ?? 0);
                        if ($usedCount < $up->package->packageServicePaid->where('service_id', $service->id)->count()) {
                            $coveredByPackage = true;
                            $userPackageIdUsed = $up->id;
                            $isFreePackageSlot = 0;
                            $packageUsageLog[$up->id][$service->id][0] = ($packageUsageLog[$up->id][$service->id][0] ?? 0) + 1;
                            break;
                        }
                    }

                    // Then check free services
                    $freeSlot = $up->package->packageServiceFree->where('service_id', $service->id)->first();
                    if ($freeSlot) {
                        $usedCount = UserUsedPackage::where('user_package_id', $up->id)->where('service_id', $service->id)->where('is_free', 1)->count() + ($packageUsageLog[$up->id][$service->id][1] ?? 0);
                        if ($usedCount < $up->package->packageServiceFree->where('service_id', $service->id)->count()) {
                            $coveredByPackage = true;
                            $userPackageIdUsed = $up->id;
                            $isFreePackageSlot = 1;
                            $packageUsageLog[$up->id][$service->id][1] = ($packageUsageLog[$up->id][$service->id][1] ?? 0) + 1;
                            break;
                        }
                    }
                }

                $bookingDetail = BookingDetail::create([
                    'booking_id' => $booking->id,
                    'service_id' => $service->id,
                    'price' =>  $service->price,
                    '_date' => $svc['date'] ?? $bookingDate,
                    'worker_id' => $svc['worker_id'],
                    'is_free' => ($is_free || $coveredByPackage),
                    'tip' =>  $current_tip,
                    'from_time' => $svc['from_time'],
                    'to_time' => $svc['to_time'],
                    'commission' => $svc['commission'] ?? null,
                    'commission_type' => $svc['commission_type'] ?? null,
                    'booking_source' => $item['booking_source'] ?? 'inside_booking',
                    'status' => ($item['booking_source'] ?? 'inside_booking') === 'outside_booking' ? 'pending' : 'confirmed',
                ]);

                if ($coveredByPackage) {
                    UserUsedPackage::create([
                        'user_id' => $clientId,
                        'user_package_id' => $userPackageIdUsed,
                        'booking_id' => $booking->id,
                        'service_id' => $service->id,
                        'is_free' => $isFreePackageSlot
                    ]);
                }
            }
        } else {
            if (empty($item['worker_id']) || empty($item['from_time']) || empty($item['to_time'])) {
                throw new \Exception('Worker and booking time are required');
            }
            $service = Service::find($item['id'] ?? null);
            if (!$service) {
                throw new \Exception('Service not found');
            }
            $current_tip = 0;
            if ($tip > 0 && ($tipWorkerId == null || $tipWorkerId == $item['worker_id'])) {
                $current_tip = $tip;
                $tip = 0; // Use tip only once
            }

            $coveredByPackage = false;
            $userPackageIdUsed = null;
            $isFreePackageSlot = 0;

            foreach ($userPackages as $up) {
                // First check paid services
                $paidSlot = $up->package->packageServicePaid->where('service_id', $service->id)->first();
                if ($paidSlot) {
                    $usedCount = UserUsedPackage::where('user_package_id', $up->id)->where('service_id', $service->id)->where('is_free', 0)->count() + ($packageUsageLog[$up->id][$service->id][0] ?? 0);
                    if ($usedCount < $up->package->packageServicePaid->where('service_id', $service->id)->count()) {
                        $coveredByPackage = true;
                        $userPackageIdUsed = $up->id;
                        $isFreePackageSlot = 0;
                        $packageUsageLog[$up->id][$service->id][0] = ($packageUsageLog[$up->id][$service->id][0] ?? 0) + 1;
                        break;
                    }
                }

                // Then check free services
                $freeSlot = $up->package->packageServiceFree->where('service_id', $service->id)->first();
                if ($freeSlot) {
                    $usedCount = UserUsedPackage::where('user_package_id', $up->id)->where('service_id', $service->id)->where('is_free', 1)->count() + ($packageUsageLog[$up->id][$service->id][1] ?? 0);
                    if ($usedCount < $up->package->packageServiceFree->where('service_id', $service->id)->count()) {
                        $coveredByPackage = true;
                        $userPackageIdUsed = $up->id;
                        $isFreePackageSlot = 1;
                        $packageUsageLog[$up->id][$service->id][1] = ($packageUsageLog[$up->id][$service->id][1] ?? 0) + 1;
                        break;
                    }
                }
            }

            $bookingDetail = BookingDetail::create([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'price' =>  $service->price,
                '_date' => $item['date'],
                'tip' =>  $current_tip,
                'is_free' => ($is_free || $coveredByPackage),
                'worker_id' => $item['worker_id'],
                'from_time' => $item['from_time'],
                'to_time' => $item['to_time'],
                'commission' => $item['commission'] ?? null,
                'commission_type' => $item['commission_type'] ?? null,
                'booking_source' => $item['booking_source'] ?? 'inside_booking',
                'status' => ($item['booking_source'] ?? 'inside_booking') === 'outside_booking' ? 'pending' : 'confirmed',
            ]);

            if ($coveredByPackage) {
                UserUsedPackage::create([
                    'user_id' => $clientId,
                    'user_package_id' => $userPackageIdUsed,
                    'booking_id' => $booking->id,
                    'service_id' => $service->id,
                    'is_free' => $isFreePackageSlot
                ]);
            }
        }

        // Calculate monetary discount amount
        $bookingOriginalTotal = 0;
        $bookingTotalCalc = 0;
        if (!empty($services) && is_array($services)) {
            foreach ($services as $svc) {
                $bookingOriginalTotal += (float) ($svc['original_price'] ?? ($svc['price'] ?? 0));
                $bookingTotalCalc += (float) ($svc['price'] ?? 0);
            }
        } else {
            $bookingOriginalTotal = (float) ($item['original_price'] ?? ($item['price'] ?? 0));
            $bookingTotalCalc = (float) ($item['price'] ?? 0);
        }
        $discountAmountAED = max(0, $bookingOriginalTotal - $bookingTotalCalc);

        // Handle wallet payment - deduct booking amount from wallet balance
        if (!empty($item['wallet_id'])) {
            $this->deductWalletBalance($item['wallet_id'], $item['client_mobile'], $bookingTotal, $booking->id, $branchId);
        }

        // Handle membership payment - deduct booking amount from membership balance
        if (!empty($item['membership_id'])) {
            $membership = Membership::find($item['membership_id']);
            if ($membership) {
                $userId = null;
                if (!empty($item['client_mobile'])) {
                    $user = User::where('phone', $item['client_mobile'])->first();
                    if ($user) {
                        $userId = $user->id;
                    }
                }

                UserUsedCard::create([
                    'code' => $membership->membership_no,
                    'amount' => $membership->percent,
                    'user_id' => $userId,
                    'membershipcards_id' => $membership->id,
                    'booking_id' => $booking->id,
                ]);
            }
        }

        // Handle discount code - create UserUsedDiscount record for daily report tracking
        if (!empty($item['discount_id'])) {
            $this->recordDiscountUsage($item['discount_id'], $item['client_mobile'] ?? null, $booking->id, $discountAmountAED);
        }

        return $booking;
    }

    /**
     * Deduct booking amount from wallet balance
     */
    private function deductWalletBalance($walletId, $clientMobile, $bookingAmount, $bookingId, $branchId)
    {
        $wallet = Wallet::find($walletId);
        if (!$wallet) {
            return; // Wallet not found, skip deduction
        }

        // Find user by phone number
        if (empty($clientMobile)) {
            return; // No mobile number, can't find user
        }

        $user = User::where('phone', $clientMobile)->first();
        if (!$user) {
            return; // User not found, skip deduction
        }

        // Create UserUsedWallet record to track wallet usage
        UserUsedWallet::create([
            'amount' => $bookingAmount, // Amount deducted from wallet
            'user_id' => $user->id,
            'branch_id' => $branchId,
            'wallet_id' => $walletId,
            'booking_id' => $bookingId,
        ]);

        // Deduct booking amount from user's wallet balance
        $currentBalance = (float) ($user->wallet ?? 0);
        $newBalance = max(0, $currentBalance - $bookingAmount); // Ensure balance doesn't go negative
        
        $user->update([
            'wallet' => $newBalance
        ]);

        // Mark wallet as used if balance reaches zero or below
        if ($newBalance <= 0 && !$wallet->used) {
            $wallet->update(['used' => true]);
        }
    }

    /**
     * Record discount code usage for daily report tracking
     */
    private function recordDiscountUsage($discountId, $clientMobile, $bookingId, $discountAmountAED = null)
    {
        $discount = Discount::find($discountId);
        if (!$discount) {
            return; // Discount not found, skip
        }

        $userId = null;
        if (!empty($clientMobile)) {
            $user = User::where('phone', $clientMobile)->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        UserUsedDiscount::create([
            'code' => $discount->code,
            'amount' => $discount->amount,
            'type' => $discount->type,
            'user_id' => $userId,
            'discountcode_id' => $discount->id,
            'booking_id' => $bookingId,
        ]);
    }

    /**
     * Create BuyProduct from cart items (all products in one BuyProduct)
     */
    private function createBuyProductFromCartItems($productItems, $saleId, $paymentType = null)
    {
        if (empty($productItems)) {
            return null;
        }

        // Get common fields from first product (they should all have same discount, etc.)
        $firstProduct = $productItems[0];
        
        $buyProduct = BuyProduct::create([
            'payment_type' => $paymentType, // Use payment type from payment section (sale payment_type)
            'discount' => $firstProduct['discount'] ?? null,
            'commission' => $firstProduct['commission'] ?? null,
            'sales_worker_id' => $firstProduct['sales_worker_id'] ?? null,
            'worker_id' => $firstProduct['worker_id'] ?? null,
            'created_by' => auth('center_user')->id() ?? auth('center_api')->id(),
            'sale_id' => $saleId,
        ]);

        // Create BuyProductDetail for each product (create multiple records based on quantity)
        foreach ($productItems as $item) {
            $product = Product::find($item['id']);
            // Use price from cart (already calculated as retail_price or supply_price)
            $price = $item['price'];
            $quantity = $item['quantity'] ?? 1;
            
            // Create one detail record per quantity unit
            for ($i = 0; $i < $quantity; $i++) {
                BuyProductDetail::create([
                    'buy_product_id' => $buyProduct->id,
                    'product_id' => $product->id,
                    'price' => $price,
                ]);
            }
        }

        return $buyProduct;
    }


    /**
     * Resolve which branch a POS sale should use (stock, bookings, etc.).
     */
    public function resolveSaleBranchId(array $cartData, array $overrides = []): ?int
    {
        if (!empty($overrides['branch_id'])) {
            return (int) $overrides['branch_id'];
        }

        $centerUserBranchId = auth('center_user')->user()?->branch_id;
        if ($centerUserBranchId) {
            return (int) $centerUserBranchId;
        }

        if (!empty($cartData['client_id'])) {
            $clientBranchId = User::where('id', $cartData['client_id'])->value('branch_id');
            if ($clientBranchId) {
                return (int) $clientBranchId;
            }
        }

        return Branch::query()->value('id');
    }

    /**
     * Validate product stock availability
     */
    private function validateProductStock($productId, $quantity, $branchId)
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new \Exception('Product not found');
        }

        $productBranch = ProductBranch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        // Enforce stock limits when tracking is enabled or branch stock is configured
        if (!$product->track_stock && !$productBranch) {
            return;
        }

        if (!$productBranch) {
            throw new \Exception('Product not available in this branch');
        }

        if ($productBranch->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$productBranch->stock_quantity}, Requested: {$quantity}");
        }
    }

    /**
     * Update product stock after sale
     */
    private function updateProductStock($productId, $quantity, $branchId, $sale = null)
    {
        $product = Product::with('primarySku')->find($productId);

        if (!$product) {
            return;
        }

        $productBranch = ProductBranch::withTrashed()
            ->where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if (!$productBranch) {
            return;
        }

        if ($productBranch->trashed()) {
            $productBranch->restore();
        }

        $productBranch->decrement('stock_quantity', (int) $quantity);

        app(InventoryMovementService::class)->record(
            (int) $productId,
            (int) $branchId,
            -((int) $quantity),
            InventoryMovement::TYPE_SALE,
            $sale,
            $product->primarySku?->id,
            'POS product sale'
        );
    }

    /**
     * Create Wallet from cart item
     */
    private function createWalletFromCartItem($item)
    {
        // Generate unique code for wallet
        $code = 'WLT' . strtoupper(uniqid());
        
        $wallet = Wallet::create([
            'code' => $code,
            'amount' => $item['amount'] ?? 0,
            'invoiced_amount' => $item['invoiced_amount'] ?? 0,
            'start_at' => $item['start_at'] ?? null,
            'end_at' => $item['end_at'] ?? null,
            'used' => false,
            'status' => true,
            'created_by' => auth('center_user')->id() ?? auth('center_api')->id(),
        ]);

        return $wallet;
    }

    /**
     * Create or get UserWallet from cart item (assign existing wallet to user)
     * Note: UserWallet may already exist if it was created when added to cart
     */
    private function createUserWalletFromCartItem($item, $branchId)
    {
        // Validate required fields
        if (empty($item['wallet_id'])) {
            throw new \Exception('Wallet ID is required for user wallet assignment');
        }
        if (empty($item['user_id'])) {
            throw new \Exception('User ID is required for user wallet assignment');
        }

        // Check if wallet exists
        $wallet = Wallet::find($item['wallet_id']);
        if (!$wallet) {
            throw new \Exception('Wallet not found');
        }

        // Check if user exists
        $user = User::find($item['user_id']);
        if (!$user) {
            throw new \Exception('User not found');
        }

        // Check if this wallet is already assigned to this user
        $existingUserWallet = UserWallet::where('wallet_id', $item['wallet_id'])
            ->where('user_id', $item['user_id'])
            ->first();

        // If UserWallet already exists (created when added to cart), just return it
        // No need to update wallet or user balance as it was already done
        if ($existingUserWallet) {
            return $existingUserWallet;
        }

        // Create UserWallet record if it doesn't exist
        $userWallet = UserWallet::create([
            'wallet_id' => $item['wallet_id'],
            'user_id' => $item['user_id'],
            'wallet_type' => $item['wallet_type'] ?? null,
            'amount' => $item['amount'] ?? $wallet->amount,
            'invoiced_amount' => $item['invoiced_amount'] ?? $wallet->invoiced_amount,
            'commission' => $item['commission'] ?? null,
            'worker_id' => $item['worker_id'] ?? null,
            'branch_id' => $branchId,
            'created_by' => auth('center_user')->id() ?? auth('center_api')->id(),
        ]);

        // Mark wallet as used (only if not already used)
        if (!$wallet->used) {
            $wallet->update(['used' => true]);
        }

        // Update user's wallet balance (only if UserWallet is newly created)
        $user->refresh();
        $user->update([
            'wallet' => ($user->wallet ?? 0) + ($userWallet->amount ?? 0)
        ]);

        return $userWallet;
    }
}

