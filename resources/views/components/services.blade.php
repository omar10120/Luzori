<!-- Services Component -->
<section class="py-5" style="background-color: #212529;" id="services">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Services Header -->
                <div class="text-center mb-5">
                    <h6 class="text-warning text-uppercase fw-bold mb-3" style="letter-spacing: 2px;">{{ __('website.services') }}</h6>
                    <h2 class="display-8 fw-bold text-white mb-4">
                        {!! __('website.services_title') !!}
                    </h2>
                    <p class="fs-5 text-light mb-5 lh-lg">
                        {{ __('website.services_subtitle') }}
                    </p>
                </div>
                
                <!-- Service Category Tabs -->
                <div class="text-center mb-5 ">
                    <div class="btn-group" role="group" aria-label="Service categories">
                        <button type="button" class="btn btn-outline-warning me-4 px-4 py-2 rounded-pill" onclick="filterServices('design')" id="design-tab">
                            {{ __('website.design') }}
                        </button>
                        <button type="button" class="btn btn-warning me-4 px-4 py-2 rounded-pill active" onclick="filterServices('all')" id="all-tab">
                            {{ __('website.all_services') }}
                        </button>
                        <button type="button" class="btn btn-outline-warning px-4 py-2 me-4 rounded-pill" onclick="filterServices('development')" id="development-tab">
                            {{ __('website.development') }}
                        </button>
                    </div>
                </div>
                
                <!-- Service Cards Grid -->
                <div class="row g-4" id="services-grid">
                    @php
                        $services = [
                            [
                                'category' => 'design',
                                'title' => __('website.appointments_and_reservations'),
                                'description' => __('website.appointments_and_reservations_desc'),
                                'icon' => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z',
                                'offers' => [__('website.online_booking'), __('website.calendar_management'), __('website.appointment_reminders')]
                            ],
                            [
                                'category' => 'design',
                                'title' => __('website.customer_management'),
                                'description' => __('website.customer_management_description'),
                                'icon_img' => 'assets/icons/Electronic wallet.svg',
                                'offers' => [__('website.customer_profiles'), __('website.visit_history'), __('website.preferences_management')]
                            ],
                            [
                                'category' => 'design',
                                'title' => __('website.employee_management'),
                                'description' => __('website.employee_management_description'),
                                'icon_img' => 'assets/icons/Employee management.svg',
                                'offers' => [__('website.staff_scheduling'), __('website.task_assignment'), __('website.commission_calculation')]
                            ],
                            [
                                'category' => 'development',
                                'title' => __('website.bills_and_payment'),
                                'description' => __('website.bills_and_payment_description'),
                                'icon_img' => 'assets/icons/Bills and Payment.svg',
                                'offers' => [__('website.invoice_generation'), __('website.payment_processing'), __('website.multi_payment_methods')]
                            ],
                            [
                                'category' => 'development',
                                'title' => __('website.stock'),
                                'description' => __('website.stock_description'),
                                'icon_img' => 'assets/icons/stock.svg',
                                'offers' => [__('website.inventory_tracking'), __('website.low_stock_alerts'), __('website.product_management')]
                            ],
                            [
                                'category' => 'development',
                                'title' => __('website.reports_and_analysis'),
                                'description' => __('website.reports_and_analysis_description'),
                                'icon_img' => 'assets/icons/Reports and Analysis.svg',
                                'offers' => [__('website.revenue_reports'), __('website.booking_analytics'), __('website.service_popularity')]
                            ],
                            [
                                'category' => 'development',
                                'title' => __('website.marketing_and_offers'),
                                'description' => __('website.marketing_and_offers_description'),
                                'icon_img' => 'assets/icons/Appointments and reservations management.svg',
                                'offers' => [__('website.sms_marketing'), __('website.loyalty_programs'), __('website.promotional_campaigns')]
                            ],
                            [
                                'category' => 'development',
                                'title' => __('website.electronic_wallet'),
                                'description' => __('website.electronic_wallet_description'),
                                'icon_img' => 'assets/icons/Electronic wallet.svg',
                                'offers' => [__('website.secure_payments'), __('website.data_encryption'), __('website.digital_wallet')]
                            ],
                            [
                                'category' => 'development',
                                'title' => __('website.celebrity_cards'),
                                'description' => __('website.celebrity_cards_description'),
                                'icon_img' => 'assets/icons/Marketing and Offers.svg',
                                'offers' => [__('website.celebrity_benefits'), __('website.discount_system'), __('website.service_packages')]
                            ],
                            [
                                'category' => 'development',
                                'title' => __('website.packages'),
                                'description' => __('website.packages_description'),
                                'icon_img' => 'assets/icons/customer-service.svg',
                                'offers' => [__('website.service_packages'), __('website.bundle_offers'), __('website.discount_system')]
                            ],

                        ];
                    @endphp
                    
                    @foreach($services as $index => $service)
                        <div class="col-md-6 col-lg-4 service-card" data-category="{{ $service['category'] }}">
                            <div class="card h-100 border-0 rounded-3" style="background-color: #343a40; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                <div class="card-body text-center p-4">
                                    <!-- Icon -->
                                    <div class="mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: rgba(255, 193, 7, 0.1); border: 2px solid #ffc107;">
                                            @if(isset($service['icon_img']))
                                                <img src="{{ asset($service['icon_img']) }}" alt="icon" style="width: 40px; height: 40px; object-fit: contain;">
                                            @else
                                                <svg width="40" height="40" fill="currentColor" viewBox="0 0 24 24" class="text-warning">
                                                    <path d="{{ $service['icon'] }}"/>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Title -->
                                    <h5 class="card-title text-white fw-bold mb-3">{{ $service['title'] }}</h5>
                                    
                                    <!-- Description -->
                                    <p class="card-text text-light mb-4">
                                        {{ $service['description'] }}
                                    </p>
                                    
                                    <!-- What we offer -->
                                    <div class="{{ app()->getLocale() == 'ar' ? 'text-end' : 'text-start' }}">
                                        <h6 class="text-warning fw-bold mb-3">{{ __('website.what_we_offer') }}</h6>
                                        <ul class="list-unstyled text-light">
                                            @foreach($service['offers'] as $offer)
                                                <li class="mb-2">• {{ $offer }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function filterServices(category) {
    // Remove active class from all tabs
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active', 'btn-warning');
        btn.classList.add('btn-outline-warning');
    });
    
    // Add active class to clicked tab
    const activeTab = document.getElementById(category + '-tab');
    activeTab.classList.add('active', 'btn-warning');
    activeTab.classList.remove('btn-outline-warning');
    
    // Filter service cards
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
            card.style.animation = 'fadeInUp 0.5s ease-out';
        } else {
            card.style.display = 'none';
        }
    });
}

// Initialize with all services visible
document.addEventListener('DOMContentLoaded', function() {
    filterServices('all');
});
</script>

<style>
/* Service Cards Animation */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.service-card {
    animation: fadeInUp 0.6s ease-out;
}

.service-card:nth-child(1) { animation-delay: 0.1s; }
.service-card:nth-child(2) { animation-delay: 0.2s; }
.service-card:nth-child(3) { animation-delay: 0.3s; }
.service-card:nth-child(4) { animation-delay: 0.4s; }
.service-card:nth-child(5) { animation-delay: 0.5s; }
.service-card:nth-child(6) { animation-delay: 0.6s; }

/* Tab Button Styles */
.btn-group .btn {
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

/* Card Hover Effects */
.card {
    border: 1px solid rgba(255, 193, 7, 0.1);
}

.card:hover {
    border-color: rgba(255, 193, 7, 0.3);
}

/* Icon Animation */
.card:hover .rounded-circle {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}
</style>
