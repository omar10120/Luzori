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

            foreach ($cartData['items'] as $item) {
                if ($item['type'] === 'service') {
                    $serviceItems[] = $item;
                } elseif ($item['type'] === 'product') {
                    // Validate stock before processing
                    $this->validateProductStock($item['id'], $item['quantity'], $branchId);
                    $productItems[] = $item;
                } elseif ($item['type'] === 'wallet') {
                    $walletItems[] = $item;
                }
            }

            // Process service items (create booking for each)
            foreach ($serviceItems as $item) {
                // Use payment_type from item (selected in booking wizard)
                $booking = $this->createBookingFromCartItem($item, $sale->id, $branchId, $item['payment_type'] ?? null);
                
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_type' => 'booking',
                    'itemable_id' => $booking->id,
                    'itemable_type' => 'App\Models\Booking',
                    'quantity' => 1,
                    'price' => $item['price'],
                    'subtotal' => $item['price'],
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
                $subtotal += $item['price'];
            } elseif ($item['type'] === 'product') {
                $subtotal += $item['price'] * $item['quantity'];
            } elseif ($item['type'] === 'wallet') {
                // Include wallet/coupon amount in subtotal
                $subtotal += $item['amount'] ?? 0;
            }
        }
        return $subtotal;
    }

    /**
     * Create Booking from cart item
     */
    private function createBookingFromCartItem($item, $saleId, $branchId, $paymentType = null)
    {
        // Validate required fields
        if (empty($item['date'])) {
            throw new \Exception('Booking date is required');
        }
        if (empty($item['worker_id'])) {
            throw new \Exception('Worker is required for booking');
        }
        if (empty($item['from_time']) || empty($item['to_time'])) {
            throw new \Exception('Booking time is required');
        }

        $booking = Booking::create([
            'booking_date' => $item['date'],
            'full_name' => $item['client_name'] ?? 'Walk-in',
            'mobile' => $item['client_mobile'] ?? null,
            'payment_type' => $paymentType ?? 'Cash',
            'branch_id' => $branchId,
            'user_id' => null,
            'sale_id' => $saleId,
            'created_by' => auth('center_user')->id() ?? auth('center_api')->id(),
        ]);

        // Create BookingDetail
        $service = Service::find($item['id']);
        
        if (!$service) {
            throw new \Exception('Service not found');
        }
        
        BookingDetail::create([
            'booking_id' => $booking->id,
            'service_id' => $service->id,
            'price' => $service->price,
            '_date' => $item['date'],
            'worker_id' => $item['worker_id'],
            'from_time' => $item['from_time'],
            'to_time' => $item['to_time'],
            'commission' => $item['commission'] ?? null,
            'commission_type' => $item['commission_type'] ?? null,
        ]);

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
}

