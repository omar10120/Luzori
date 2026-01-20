<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Worker;
use App\Models\UserWallet;
use App\Models\BuyProduct;
use App\Models\Service;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Product;
use Carbon\Carbon;
use App\Datatables\CenterUser\ServiceDataTable;

class HomeController extends Controller
{
    public function cp()
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');
        
        // Get statistics
        $statistics = $this->getStatistics($today, $thisMonth);
        
        return view('CenterUser.SubViews.cp', compact('statistics'));
    }
    
    private function getStatistics($today, $thisMonth)
    {
        return [
            // Main Statistics
            'services_count' => $this->getServicesCount(),
            'customers_count' => $this->getCustomersCount(),
            'today_bookings_count' => $this->getTodayBookingsCount($today),
            'today_revenue' => $this->getTodayRevenue($today),
            
            // Additional Statistics
            'active_coupons_count' => $this->getActiveCouponsCount(),
            'active_workers_count' => $this->getActiveWorkersCount(),
            'available_products_count' => $this->getAvailableProductsCount(),
            
            // Top Performers
            'best_service' => $this->getBestService($thisMonth),
            'best_worker' => $this->getBestWorker($thisMonth),
            'best_customer' => $this->getBestCustomer($thisMonth),
        ];
    }
    
    private function getServicesCount()
    {
        return Service::count();
    }
    
    private function getCustomersCount()
    {
        return User::count();
    }
    
    private function getTodayBookingsCount($today)
    {
        return Booking::whereRaw('DATE(booking_date)="' . $today . '"')->count();
    }
    
    private function getTodayRevenue($today)
    {
        // Reuse DailyReportController revenue calculation logic
        $revenue = 0;
        
        // 1. Revenue from Bookings
        $bookings = Booking::whereRaw('DATE(booking_date)="' . $today . '"')
            ->with('details')
            ->get();
            
        foreach ($bookings as $booking) {
            if (!empty($booking->details)) {
                foreach ($booking->details as $detail) {
                    $revenue += $detail->price;
                }
            }
        }
        
        // 2. Revenue from BuyProduct
        $buyProducts = BuyProduct::select('buy_products.*')
            ->with('details')
            ->join('workers', 'workers.id', '=', 'buy_products.worker_id')
            ->whereRaw('DATE(buy_products.created_at)="' . $today . '"')
            ->get();
            
        foreach ($buyProducts as $buyProduct) {
            if (!$buyProduct->details->isEmpty()) {
                foreach ($buyProduct->details as $detail) {
                    $product_price = $detail->price;
                    if (!empty($buyProduct->discount)) {
                        $product_price -= ($product_price * $buyProduct->discount) / 100;
                    }
                    $revenue += $product_price;
                }
            }
        }
        
        // 3. Revenue from UserWallet
        $wallets = UserWallet::whereRaw('DATE(users_wallets.created_at)="' . $today . '"')
            ->get();
            
        foreach ($wallets as $wallet) {
            $revenue += $wallet->invoiced_amount;
        }
        
        return $revenue;
    }
    
    private function getActiveCouponsCount()
    {
        $totalCoupons = wallet::count();
        $activeCoupons = wallet::where('used', 1)->count();
        
        if ($totalCoupons > 0) {
            return round(($activeCoupons / $totalCoupons) * 100);
        }
        
        return 0;
    }
    
    private function getActiveWorkersCount()
    {
        return Worker::count();
    }
    
    private function getAvailableProductsCount()
    {
        return Product::count();
    }
    
    private function getBestService($thisMonth)
    {
        $serviceCounts = Booking::with('details.service')
            ->whereRaw('DATE_FORMAT(booking_date, "%Y-%m")="' . $thisMonth . '"')
            ->get()
            ->flatMap(function ($booking) {
                return $booking->details->map(function ($detail) {
                    return $detail->service_id;
                });
            })
            ->countBy()
            ->sortDesc();
            
        if ($serviceCounts->isNotEmpty()) {
            $serviceId = $serviceCounts->keys()->first();
            $count = $serviceCounts->first();
            $service = Service::find($serviceId);
            
            return [
                'name' => $service ? $service->name : 'Unknown',
                'count' => $count
            ];
        }
        
        return ['name' => 'No Data', 'count' => 0];
    }
    
    private function getBestWorker($thisMonth)
    {
        $workerCounts = Booking::with('details')
            ->whereRaw('DATE_FORMAT(booking_date, "%Y-%m")="' . $thisMonth . '"')
            ->get()
            ->flatMap(function ($booking) {
                return $booking->details->map(function ($detail) {
                    return $detail->worker_id;
                });
            })
            ->countBy()
            ->sortDesc();
            
        if ($workerCounts->isNotEmpty()) {
            $workerId = $workerCounts->keys()->first();
            $count = $workerCounts->first();
            $worker = Worker::find($workerId);
            
            return [
                'name' => $worker ? $worker->name : 'Unknown',
                'count' => $count
            ];
        }
        
        return ['name' => 'No Data', 'count' => 0];
    }
    

    private function getBestCustomer($thisMonth)
    {
        $bestCustomer = Booking::whereRaw('DATE_FORMAT(booking_date, "%Y-%m")="' . $thisMonth . '"')
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as booking_count')
            ->groupBy('user_id')
            ->orderBy('booking_count', 'desc')
            ->first();
            
        if ($bestCustomer && $bestCustomer->user_id) {
            $user = User::find($bestCustomer->user_id);
            if ($user) {
                $fullName = trim($user->first_name . ' ' . $user->last_name);
                return [
                    'name' => $fullName ?: 'Unknown User',
                    'count' => $bestCustomer->booking_count
                ];
            } else {
                // User ID exists but user not found (might be soft deleted)
                return [
                    'name' => 'User #' . $bestCustomer->user_id . ' (Deleted)',
                    'count' => $bestCustomer->booking_count
                ];
            }
        }
        
        // If no registered users, check for walk-in customers
        $walkInCustomer = Booking::whereRaw('DATE_FORMAT(booking_date, "%Y-%m")="' . $thisMonth . '"')
            ->whereNull('user_id')
            ->whereNotNull('full_name')
            ->selectRaw('full_name, COUNT(*) as booking_count')
            ->groupBy('full_name')
            ->orderBy('booking_count', 'desc')
            ->first();
            
        if ($walkInCustomer) {
            return [
                'name' => $walkInCustomer->full_name,
                'count' => $walkInCustomer->booking_count
            ];
        }
        
        return ['name' => 'No Data', 'count' => 0];
    }
    
    public function getDetails($type)
    {
        switch ($type) {
            case 'services':
                return $this->getServicesDetails();
            case 'customers':
                return $this->getCustomersDetails();
            case 'bookings':
                return $this->getBookingsDetails();
            case 'revenue':
                return $this->getRevenueDetails();
            case 'coupons':
                return $this->getActiveCouponsDetails();
            case 'workers':
                return $this->getActiveWorkersDetails();
            case 'products':
                return $this->getAvailableProductsDetails();
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown type'
                ]);
        }
    }
    
    public function getServicesDetails()
    {
        $services = Service::with(['translation'])->paginate(10);
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th>#</th>';
        $html .= '<th>' . __('field.image') . '</th>';
        $html .= '<th>' . __('field.name') . '</th>';
        $html .= '<th>' . __('field.rooms_no') . '</th>';
        $html .= '<th>' . __('field.free_book') . '</th>';
        $html .= '<th>' . __('field.price') . '</th>';
        $html .= '<th>' . __('field.sort_order') . '</th>';
        $html .= '<th>' . __('field.is_top') . '</th>';
        $html .= '<th>' . __('field.created_at') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($services as $index => $service) {
            $html .= '<tr>';
            $html .= '<td>' . ($services->firstItem() + $index) . '</td>';
            $html .= '<td><img src="' . $service->image . '" style="width:50px;height:50px;" class="rounded"></td>';
            $html .= '<td>' . ($service->translation ? $service->translation->name : $service->name) . '</td>';
            $html .= '<td>' . $service->rooms_no . '</td>';
            $html .= '<td>' . $service->free_book . '</td>';
            $html .= '<td>' . $service->price . ' ' . trim(get_currency()) . '</td>';
            $html .= '<td>' . $service->sort_order . '</td>';
            $html .= '<td>' . ($service->is_top ? __('general.yes') : __('general.no')) . '</td>';
            $html .= '<td>' . \Carbon\Carbon::parse($service->created_at)->format('Y-m-d H:i') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Add pagination
        $html .= '<div class="d-flex justify-content-between align-items-center mt-3">';
        $html .= '<div class="text-muted">';
        $html .= __('general.showing') . ' ' . $services->firstItem() . ' ' . __('general.to') . ' ' . $services->lastItem() . ' ' . __('general.of') . ' ' . $services->total() . ' ' . __('field.services');
        $html .= '</div>';
        $html .= '<nav aria-label="Services pagination">';
        $html .= $services->links('pagination::bootstrap-4');
        $html .= '</nav>';
        $html .= '</div>';
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    public function getCustomersDetails()
    {
        $customers = User::where('role_id', 3)->paginate(10);
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th>#</th>';
        $html .= '<th>' . __('field.name') . '</th>';
        $html .= '<th>' . __('field.email') . '</th>';
        $html .= '<th>' . __('field.phone') . '</th>';
        $html .= '<th>' . __('field.status') . '</th>';
        $html .= '<th>' . __('field.created_at') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($customers as $index => $customer) {
            $html .= '<tr>';
            $html .= '<td>' . ($customers->firstItem() + $index) . '</td>';
            $html .= '<td>' . ($customer->first_name . ' ' . $customer->last_name) . '</td>';
            $html .= '<td>' . $customer->email . '</td>';
            $html .= '<td>' . ($customer->country_code . $customer->phone) . '</td>';
            $html .= '<td>';
            $html .= '<span class="badge bg-' . ($customer->deleted_at ? 'danger' : 'success') . '">';
            $html .= $customer->deleted_at ? __('general.inactive') : __('general.active');
            $html .= '</span>';
            $html .= '</td>';
            $html .= '<td>' . \Carbon\Carbon::parse($customer->created_at)->format('Y-m-d H:i') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Add pagination
        $html .= '<div class="d-flex justify-content-between align-items-center mt-3">';
        $html .= '<div class="text-muted">';
        $html .= __('general.showing') . ' ' . $customers->firstItem() . ' ' . __('general.to') . ' ' . $customers->lastItem() . ' ' . __('general.of') . ' ' . $customers->total() . ' ' . __('field.customers');
        $html .= '</div>';
        $html .= '<nav aria-label="Customers pagination">';
        $html .= $customers->links('pagination::bootstrap-4');
        $html .= '</nav>';
        $html .= '</div>';
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    public function getBookingsDetails()
    {
        $today = now()->format('Y-m-d');
        $bookings = Booking::whereRaw('DATE(booking_date)="' . $today . '"')
            ->with(['details.service.translation', 'details.worker', 'user'])
            ->paginate(10);
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th>#</th>';
        $html .= '<th>' . __('field.customer') . '</th>';
        $html .= '<th>' . __('field.service') . '</th>';
        $html .= '<th>' . __('field.worker') . '</th>';
        $html .= '<th>' . __('field.time') . '</th>';
        $html .= '<th>' . __('field.price') . '</th>';
        $html .= '<th>' . __('field.status') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($bookings as $index => $booking) {
            foreach ($booking->details as $detailIndex => $detail) {
                // Better customer name logic
                $customerName = 'N/A';
                if ($booking->user_id) {
                    if ($booking->user) {
                        $customerName = trim($booking->user->first_name . ' ' . $booking->user->last_name);
                    } else {
                        // User ID exists but user not found (might be soft deleted)
                        $customerName = 'User #' . $booking->user_id . ' (Deleted)';
                    }
                } else {
                    // No user_id, use full_name from booking
                    $customerName = $booking->full_name ?: 'Walk-in Customer';
                }
                
                $html .= '<tr>';
                $html .= '<td>' . ($bookings->firstItem() + $index) . '</td>';
                $html .= '<td>' . $customerName . '</td>';
                $html .= '<td>' . ($detail->service && $detail->service->translation ? $detail->service->translation->name : 'N/A') . '</td>';
                $html .= '<td>' . ($detail->worker ? $detail->worker->name : 'N/A') . '</td>';
                $html .= '<td>' . \Carbon\Carbon::parse($booking->booking_date)->format('H:i') . '</td>';
                $html .= '<td>' . $detail->price . ' ' . trim(get_currency()) . '</td>';
                $html .= '<td>';
                $html .= '<span class="badge bg-' . ($booking->deleted_at ? 'danger' : 'success') . '">';
                $html .= $booking->deleted_at ? __('general.cancelled') : __('general.active');
                $html .= '</span>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Add pagination
        $html .= '<div class="d-flex justify-content-between align-items-center mt-3">';
        $html .= '<div class="text-muted">';
        $html .= __('general.showing') . ' ' . $bookings->firstItem() . ' ' . __('general.to') . ' ' . $bookings->lastItem() . ' ' . __('general.of') . ' ' . $bookings->total() . ' ' . __('field.bookings');
        $html .= '</div>';
        $html .= '<nav aria-label="Bookings pagination">';
        $html .= $bookings->links('pagination::bootstrap-4');
        $html .= '</nav>';
        $html .= '</div>';
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    public function getRevenueDetails()
    {
        $today = now()->format('Y-m-d');
        
        // Get revenue from bookings
        $bookingRevenue = Booking::whereRaw('DATE(booking_date)="' . $today . '"')
            ->with('details')
            ->get()
            ->sum(function($booking) {
                return $booking->details->sum('price');
            });
        
        // Get revenue from buy products
        $productRevenue = BuyProduct::select('buy_products.*')
            ->with('details')
            ->join('workers', 'workers.id', '=', 'buy_products.worker_id')
            ->whereRaw('DATE(buy_products.created_at)="' . $today . '"')
            ->get()
            ->sum(function($buyProduct) {
                return $buyProduct->details->sum(function($detail) use ($buyProduct) {
                    $product_price = $detail->price;
                    if (!empty($buyProduct->discount)) {
                        $product_price -= ($product_price * $buyProduct->discount) / 100;
                    }
                    return $product_price;
                });
            });
        
        // Get revenue from user wallets
        $walletRevenue = UserWallet::whereRaw('DATE(users_wallets.created_at)="' . $today . '"')
            ->sum('invoiced_amount');
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th>' . __('field.revenue_source') . '</th>';
        $html .= '<th>' . __('field.amount') . '</th>';
        $html .= '<th>' . __('field.percentage') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        $totalRevenue = $bookingRevenue + $productRevenue + $walletRevenue;
        
        $sources = [
            ['name' => __('field.bookings'), 'amount' => $bookingRevenue],
            ['name' => __('field.products'), 'amount' => $productRevenue],
            ['name' => __('field.wallets'), 'amount' => $walletRevenue]
        ];
        
        foreach ($sources as $index => $source) {
            $percentage = $totalRevenue > 0 ? round(($source['amount'] / $totalRevenue) * 100, 2) : 0;
            $html .= '<tr>';
            $html .= '<td>' . $source['name'] . '</td>';
            $html .= '<td>' . number_format($source['amount'], 0) . ' ' . trim(get_currency()) . '</td>';
            $html .= '<td>' . $percentage . '%</td>';
            $html .= '</tr>';
        }
        
        $html .= '<tr class="table-success fw-bold">';
        $html .= '<td>' . __('field.total_revenue') . '</td>';
        $html .= '<td>' . number_format($totalRevenue, 0) . ' ' . trim(get_currency()) . '</td>';
        $html .= '<td>100%</td>';
        $html .= '</tr>';
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    public function getActiveCouponsDetails()
    {
        $coupons = UserWallet::with(['user', 'wallet'])->paginate(10);
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th>#</th>';
        $html .= '<th>' . __('field.customer') . '</th>';
        $html .= '<th>' . __('field.amount') . '</th>';
        $html .= '<th>' . __('field.wallet_type') . '</th>';
        $html .= '<th>' . __('field.status') . '</th>';
        $html .= '<th>' . __('field.created_at') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($coupons as $index => $coupon) {
            $html .= '<tr>';
            $html .= '<td>' . ($coupons->firstItem() + $index) . '</td>';
            $html .= '<td>' . ($coupon->user ? $coupon->user->first_name . ' ' . $coupon->user->last_name : 'N/A') . '</td>';
            $html .= '<td>' . number_format($coupon->amount, 0) . ' ' . trim(get_currency()) . '</td>';
            $html .= '<td>' . ($coupon->wallet_type ?? 'N/A') . '</td>';
            $html .= '<td>';
            $html .= '<span class="badge bg-' . ($coupon->deleted_at ? 'danger' : 'success') . '">';
            $html .= $coupon->deleted_at ? __('general.inactive') : __('general.active');
            $html .= '</span>';
            $html .= '</td>';
            $html .= '<td>' . \Carbon\Carbon::parse($coupon->created_at)->format('Y-m-d H:i') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Add pagination
        $html .= '<div class="d-flex justify-content-between align-items-center mt-3">';
        $html .= '<div class="text-muted">';
        $html .= __('general.showing') . ' ' . $coupons->firstItem() . ' ' . __('general.to') . ' ' . $coupons->lastItem() . ' ' . __('general.of') . ' ' . $coupons->total() . ' ' . __('field.coupons');
        $html .= '</div>';
        $html .= '<nav aria-label="Coupons pagination">';
        $html .= $coupons->links('pagination::bootstrap-4');
        $html .= '</nav>';
        $html .= '</div>';
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    public function getActiveWorkersDetails()
    {
        $workers = Worker::with(['branch'])->paginate(10);
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th>#</th>';
        $html .= '<th>' . __('field.name') . '</th>';
        $html .= '<th>' . __('field.email') . '</th>';
        $html .= '<th>' . __('field.phone') . '</th>';
        $html .= '<th>' . __('field.branch') . '</th>';
        $html .= '<th>' . __('field.status') . '</th>';
        $html .= '<th>' . __('field.created_at') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($workers as $index => $worker) {
            $html .= '<tr>';
            $html .= '<td>' . ($workers->firstItem() + $index) . '</td>';
            $html .= '<td>' . $worker->name . '</td>';
            $html .= '<td>' . $worker->email . '</td>';
            $html .= '<td>' . ($worker->country_code . $worker->phone) . '</td>';
            $html .= '<td>' . ($worker->branch ? $worker->branch->name : 'N/A') . '</td>';
            $html .= '<td>';
            $html .= '<span class="badge bg-' . ($worker->deleted_at ? 'danger' : 'success') . '">';
            $html .= $worker->deleted_at ? __('general.inactive') : __('general.active');
            $html .= '</span>';
            $html .= '</td>';
            $html .= '<td>' . \Carbon\Carbon::parse($worker->created_at)->format('Y-m-d H:i') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Add pagination
        $html .= '<div class="d-flex justify-content-between align-items-center mt-3">';
        $html .= '<div class="text-muted">';
        $html .= __('general.showing') . ' ' . $workers->firstItem() . ' ' . __('general.to') . ' ' . $workers->lastItem() . ' ' . __('general.of') . ' ' . $workers->total() . ' ' . __('field.workers');
        $html .= '</div>';
        $html .= '<nav aria-label="Workers pagination">';
        $html .= $workers->links('pagination::bootstrap-4');
        $html .= '</nav>';
        $html .= '</div>';
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    public function getAvailableProductsDetails()
    {
        $products = Product::with(['translation'])->paginate(10);
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-bordered table-hover">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th>#</th>';
        $html .= '<th>' . __('field.image') . '</th>';
        $html .= '<th>' . __('field.name') . '</th>';
        $html .= '<th>' . __('field.price') . '</th>';
        $html .= '<th>' . __('field.quantity') . '</th>';
        $html .= '<th>' . __('field.status') . '</th>';
        $html .= '<th>' . __('field.created_at') . '</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($products as $index => $product) {
            $html .= '<tr>';
            $html .= '<td>' . ($products->firstItem() + $index) . '</td>';
            $html .= '<td><img src="' . $product->image . '" style="width:50px;height:50px;" class="rounded"></td>';
            $html .= '<td>' . ($product->translation ? $product->translation->name : $product->name) . '</td>';
            $html .= '<td>' . $product->price . ' ' . trim(get_currency()) . '</td>';
            $html .= '<td>' . $product->quantity . '</td>';
            $html .= '<td>';
            $html .= '<span class="badge bg-' . ($product->deleted_at ? 'danger' : 'success') . '">';
            $html .= $product->deleted_at ? __('general.inactive') : __('general.active');
            $html .= '</span>';
            $html .= '</td>';
            $html .= '<td>' . \Carbon\Carbon::parse($product->created_at)->format('Y-m-d H:i') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Add pagination
        $html .= '<div class="d-flex justify-content-between align-items-center mt-3">';
        $html .= '<div class="text-muted">';
        $html .= __('general.showing') . ' ' . $products->firstItem() . ' ' . __('general.to') . ' ' . $products->lastItem() . ' ' . __('general.of') . ' ' . $products->total() . ' ' . __('field.products');
        $html .= '</div>';
        $html .= '<nav aria-label="Products pagination">';
        $html .= $products->links('pagination::bootstrap-4');
        $html .= '</nav>';
        $html .= '</div>';
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
}
