<!-- FAQ Component -->
<section class="py-5" style="background-color: #212529;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- FAQ Header -->
                <div class="text-center mb-5">
                    <h2 class="display-8 fw-bold text-white mb-4">
                        {!! __('website.faq_title') !!}
                    </h2>
                    <p class="fs-5 text-light">{{ __('website.faq_subtitle') }}</p>
                </div>
                
                <!-- FAQ Categories -->
                <div class="text-center mb-5">
                    <div class="btn-group" role="group" aria-label="FAQ categories">
                        <button type="button" class="btn btn-outline-warning me-2 px-4 py-2 rounded-pill active" onclick="filterFAQ('general')" id="general-tab">
                            {{ __('website.general') }}
                        </button>
                        <button type="button" class="btn btn-outline-warning me-2 px-4 py-2 rounded-pill" onclick="filterFAQ('services')" id="services-tab">
                            {{ __('website.services_faq') }}
                        </button>
                        <button type="button" class="btn btn-outline-warning me-2 px-4 py-2 rounded-pill" onclick="filterFAQ('support')" id="support-tab">
                            {{ __('website.support') }}
                        </button>
                        <button type="button" class="btn btn-outline-warning px-4 py-2 rounded-pill" onclick="filterFAQ('pricing')" id="pricing-tab">
                            {{ __('website.pricing') }}
                        </button>
                    </div>
                </div>
                
                <!-- FAQ Accordion -->
                <div class="faq-container">
                    @php
                        $faqs = [
                            'general' => [
                                [
                                    'question' => __('website.faq_general_1_q'),
                                    'answer' => __('website.faq_general_1_a')
                                ],
                                [
                                    'question' => __('website.faq_general_2_q'),
                                    'answer' => __('website.faq_general_2_a')
                                ],
                                [
                                    'question' => __('website.faq_general_3_q'),
                                    'answer' => __('website.faq_general_3_a')
                                ],
                                [
                                    'question' => __('website.faq_general_4_q'),
                                    'answer' => __('website.faq_general_4_a')
                                ]
                            ],
                            'services' => [
                                [
                                    'question' => __('website.faq_services_1_q'),
                                    'answer' => __('website.faq_services_1_a')
                                ],
                                [
                                    'question' => __('website.faq_services_2_q'),
                                    'answer' => __('website.faq_services_2_a')
                                ],
                                [
                                    'question' => __('website.faq_services_3_q'),
                                    'answer' => __('website.faq_services_3_a')
                                ],
                                [
                                    'question' => __('website.faq_services_4_q'),
                                    'answer' => __('website.faq_services_4_a')
                                ]
                            ],
                            'support' => [
                                [
                                    'question' => __('website.faq_support_1_q'),
                                    'answer' => __('website.faq_support_1_a')
                                ],
                                [
                                    'question' => __('website.faq_support_2_q'),
                                    'answer' => __('website.faq_support_2_a')
                                ],
                                [
                                    'question' => __('website.faq_support_3_q'),
                                    'answer' => __('website.faq_support_3_a')
                                ],
                                [
                                    'question' => __('website.faq_support_4_q'),
                                    'answer' => __('website.faq_support_4_a')
                                ]
                            ],
                            'pricing' => [
                                [
                                    'question' => __('website.faq_pricing_1_q'),
                                    'answer' => __('website.faq_pricing_1_a')
                                ],
                                [
                                    'question' => __('website.faq_pricing_2_q'),
                                    'answer' => __('website.faq_pricing_2_a')
                                ],
                                [
                                    'question' => __('website.faq_pricing_3_q'),
                                    'answer' => __('website.faq_pricing_3_a')
                                ],
                                [
                                    'question' => __('website.faq_pricing_4_q'),
                                    'answer' => __('website.faq_pricing_4_a')
                                ]
                            ]
                        ];
                    @endphp
                    
                    @foreach($faqs as $category => $categoryFAQs)
                        <div class="faq-category" id="faq-{{ $category }}" style="display: {{ $category === 'general' ? 'block' : 'none' }};">
                            @foreach($categoryFAQs as $index => $faq)
                                <div class="faq-item mb-3" style="animation-delay: {{ $index * 0.1 }}s;">
                                    <div class="card border-0 rounded-3" style="background-color: #343a40; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="card-header border-0 bg-transparent p-0">
                                            <button class="btn btn-link w-100 text-start p-4 d-flex justify-content-between align-items-center faq-toggle" onclick="toggleFAQ(this)" style="text-decoration: none; color: white;" data-bs-toggle="collapse" data-bs-target="#faq-{{ $category }}-{{ $index }}" aria-expanded="{{ $index === 0 && $category === 'general' ? 'true' : 'false' }}">
                                                <h6 class="mb-0 fw-bold">{{ $faq['question'] }}</h6>
                                                <span class="faq-icon" style="font-size: 1.5rem; color: #ffc107; transition: transform 0.3s ease;">
                                                    <span class="plus-icon">+</span>
                                                    <span class="minus-icon" style="display: none;">-</span>
                                                </span>
                                            </button>
                                        </div>
                                        <div class="collapse {{ $index === 0 && $category === 'general' ? 'show' : '' }}" id="faq-{{ $category }}-{{ $index }}">
                                            <div class="card-body pt-0 pb-4 px-4">
                                                <p class="text-light mb-0 lh-lg">{{ $faq['answer'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function filterFAQ(category) {
    // Remove active class from all tabs
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active', 'btn-warning');
        btn.classList.add('btn-outline-warning');
    });
    
    // Add active class to clicked tab
    const activeTab = document.getElementById(category + '-tab');
    activeTab.classList.add('active', 'btn-warning');
    activeTab.classList.remove('btn-outline-warning');
    
    // Hide all FAQ categories
    document.querySelectorAll('.faq-category').forEach(cat => {
        cat.style.display = 'none';
    });
    
    // Show selected category
    document.getElementById('faq-' + category).style.display = 'block';
    
    // Reset all FAQ items in the new category
    const categoryFAQs = document.querySelectorAll('#faq-' + category + ' .faq-item');
    categoryFAQs.forEach((item, index) => {
        const collapse = item.querySelector('.collapse');
        const icon = item.querySelector('.faq-icon');
        const plusIcon = item.querySelector('.plus-icon');
        const minusIcon = item.querySelector('.minus-icon');
        
        // Close all items
        collapse.classList.remove('show');
        plusIcon.style.display = 'inline';
        minusIcon.style.display = 'none';
        
        // Add entrance animation
        item.style.animation = 'fadeInUp 0.6s ease-out ' + (index * 0.1) + 's both';
    });
}

function toggleFAQ(button) {
    const collapse = button.nextElementSibling;
    const icon = button.querySelector('.faq-icon');
    const plusIcon = button.querySelector('.plus-icon');
    const minusIcon = button.querySelector('.minus-icon');
    
    // Toggle collapse
    if (collapse.classList.contains('show')) {
        collapse.classList.remove('show');
        plusIcon.style.display = 'inline';
        minusIcon.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    } else {
        collapse.classList.add('show');
        plusIcon.style.display = 'none';
        minusIcon.style.display = 'inline';
        icon.style.transform = 'rotate(180deg)';
    }
}

// Initialize with general category
document.addEventListener('DOMContentLoaded', function() {
    filterFAQ('general');
});
</script>

<style>
/* FAQ Animations */
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

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Section animations */
.faq-container {
    animation: fadeInUp 0.8s ease-out;
}

.faq-item {
    animation: fadeInUp 0.6s ease-out both;
}

/* Tab Button Styles */
.btn-group .btn {
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
}

/* FAQ Card Styles */
.faq-item .card {
    border: 1px solid rgba(255, 193, 7, 0.1);
    transition: all 0.3s ease;
}

.faq-item .card:hover {
    border-color: rgba(255, 193, 7, 0.3);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

/* FAQ Toggle Button */
.faq-toggle {
    transition: all 0.3s ease;
}

.faq-toggle:hover {
    background-color: rgba(255, 193, 7, 0.1);
}

/* Icon Animation */
.faq-icon {
    transition: transform 0.3s ease;
}

.faq-icon:hover {
    transform: scale(1.2);
}

/* Collapse Animation */
.collapse {
    transition: all 0.3s ease;
}

.collapse.show {
    animation: slideInLeft 0.3s ease-out;
}

/* Responsive Design */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 0.5rem;
        margin-right: 0 !important;
    }
    
    .faq-toggle {
        padding: 1rem !important;
    }
    
    .faq-toggle h6 {
        font-size: 0.9rem;
    }
}

/* Smooth transitions */
.faq-category {
    transition: all 0.3s ease;
}

.faq-item .card-body {
    transition: all 0.3s ease;
}

/* Hover effects */
.faq-item:hover .card {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

/* Active state styling */
.btn-warning {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #212529 !important;
}

.btn-outline-warning:hover {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}
</style>
