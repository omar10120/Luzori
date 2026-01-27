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
use App\Models\UserWallet;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class SalesService
{
    /**
     * Process cart and create sale with all related records
     */
    public function processSale($cartData)
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

            // Get branch
            $branchId = auth('center_user')->user()->branch_id ?? Branch::first()->id ?? null;
            
            if (!$branchId) {
                throw new \Exception('No branch found for this user');
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
                'created_by' => auth('center_user')->id() ?? auth('center_api')->id(),
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

            // Process service items (one booking per item; each item can have multiple services)
            foreach ($serviceItems as $item) {
                $booking = $this->createBookingFromCartItem($item, $sale->id, $branchId, $item['payment_type'] ?? null);
                $bookingSubtotal = 0;
                if (!empty($item['services']) && is_array($item['services'])) {
                    foreach ($item['services'] as $svc) {
                        $bookingSubtotal += (float) ($svc['price'] ?? 0);
                    }
                } else {
                    $bookingSubtotal = (float) ($item['price'] ?? 0);
                }
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
                    $this->updateProductStock($item['id'], $item['quantity'], $branchId);
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
                    'price' => $item['amount'] ?? 0,
                    'subtotal' => $item['amount'] ?? 0,
                ]);
            }

            DB::commit();
            return $sale;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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
                } else {
                    $subtotal += (float) ($item['price'] ?? 0);
                }
            } elseif ($item['type'] === 'product') {
                $subtotal += $item['price'] * $item['quantity'];
            } elseif ($item['type'] === 'wallet' || $item['type'] === 'user_wallet') {
                // Include wallet/coupon amount in subtotal
                $subtotal += $item['amount'] ?? 0;
            }
        }
        return $subtotal;
    }

    /**
     * Create Booking from cart item (one booking with one or more services)
     */
    private function createBookingFromCartItem($item, $saleId, $branchId, $paymentType = null)
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
            'payment_type' => $paymentType ?? 'Cash',
            'branch_id' => $branchId,
            'user_id' => null,
            'sale_id' => $saleId,
            'created_by' => auth('center_user')->id() ?? auth('center_api')->id(),
        ]);

        if (!empty($services) && is_array($services)) {
            foreach ($services as $svc) {
                if (empty($svc['worker_id']) || empty($svc['from_time']) || empty($svc['to_time'])) {
                    throw new \Exception('Worker and time are required for each service');
                }
                $service = Service::find($svc['id'] ?? null);
                if (!$service) {
                    throw new \Exception('Service not found: ' . ($svc['id'] ?? ''));
                }
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'service_id' => $service->id,
                    'price' => (float) ($svc['price'] ?? $service->price),
                    '_date' => $svc['date'] ?? $bookingDate,
                    'worker_id' => $svc['worker_id'],
                    'from_time' => $svc['from_time'],
                    'to_time' => $svc['to_time'],
                    'commission' => $svc['commission'] ?? null,
                    'commission_type' => $svc['commission_type'] ?? null,
                ]);
            }
        } else {
            if (empty($item['worker_id']) || empty($item['from_time']) || empty($item['to_time'])) {
                throw new \Exception('Worker and booking time are required');
            }
            $service = Service::find($item['id'] ?? null);
            if (!$service) {
                throw new \Exception('Service not found');
            }
            BookingDetail::create([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'price' => (float) ($item['price'] ?? $service->price),
                '_date' => $item['date'],
                'worker_id' => $item['worker_id'],
                'from_time' => $item['from_time'],
                'to_time' => $item['to_time'],
                'commission' => $item['commission'] ?? null,
                'commission_type' => $item['commission_type'] ?? null,
            ]);
        }

        return $booking;
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
     * Validate product stock availability
     */
    private function validateProductStock($productId, $quantity, $branchId)
    {
        $product = Product::find($productId);
        
        if (!$product->track_stock) {
            return; // Stock tracking disabled
        }

        $productBranch = ProductBranch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if (!$productBranch) {
            throw new \Exception("Product not available in this branch");
        }

        if ($productBranch->stock_quantity < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$productBranch->stock_quantity}, Requested: {$quantity}");
        }
    }

    /**
     * Update product stock after sale
     */
    private function updateProductStock($productId, $quantity, $branchId)
    {
        $product = Product::find($productId);
        
        if (!$product->track_stock) {
            return; // Stock tracking disabled
        }

        $productBranch = ProductBranch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if ($productBranch) {
            $productBranch->stock_quantity -= $quantity;
            $productBranch->save();
        }
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

