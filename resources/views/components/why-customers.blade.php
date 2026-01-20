<!-- Why Customers Component -->
<section class="py-5" style="background-color: #212529;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Section Header -->
                <div class="text-center mb-5">
                    <h2 class="display-8 fw-bold text-white mb-4">
                        {!! __('website.why_customers_title') !!}
                    </h2>
                    <p class="fs-5 text-light">{{ __('website.why_customers_subtitle') }}</p>
                </div>
                
                <!-- Testimonial Carousel -->
                <div class="testimonial-container position-relative">
                    <!-- Navigation Arrows -->
                    <button class="testimonial-nav testimonial-prev position-absolute top-50 start-0 translate-middle-y" onclick="changeTestimonial(-1)" style="z-index: 10; background: none; border: none; color: #ffc107; font-size: 2rem; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-50%) scale(1.2)'" onmouseout="this.style.transform='translateY(-50%) scale(1)'">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                        </svg>
                    </button>
                    
                    <button class="testimonial-nav testimonial-next position-absolute top-50 end-0 translate-middle-y" onclick="changeTestimonial(1)" style="z-index: 10; background: none; border: none; color: #ffc107; font-size: 2rem; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-50%) scale(1.2)'" onmouseout="this.style.transform='translateY(-50%) scale(1)'">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/>
                        </svg>
                    </button>
                    
                    <!-- Testimonial Card -->
                    <div class="testimonial-card mx-auto position-relative" style="max-width: 800px; background-color: #000; border-radius: 15px; padding: 3rem; min-height: 200px; transition: all 0.5s ease;">
                        <!-- Opening Quote -->
                        <div class="position-absolute top-0 start-0" style="font-size: 4rem; color: #ffc107; line-height: 1; transform: translate(-10px, -10px);">
                            "
                        </div>
                        
                        <!-- Testimonial Content -->
                        <div class="testimonial-content text-center">
                            <p class="fs-4 text-white mb-0 lh-lg testimonial-text" id="testimonial-text">
                                {{ __('website.testimonial_2') }}
                            </p>
                        </div>
                        
                        <!-- Closing Quote -->
                        <div class="position-absolute bottom-0 end-0" style="font-size: 4rem; color: #ffc107; line-height: 1; transform: translate(10px, 10px);">
                            "
                        </div>
                    </div>
                </div>
                
                <!-- Pagination Dots -->
                <div class="text-center mt-4">
                    <div class="pagination-dots d-flex justify-content-center align-items-center gap-2">
                        @php
                            $testimonials = [
                                __('website.testimonial_1'),
                                __('website.testimonial_2'),
                                __('website.testimonial_3'),
                                __('website.testimonial_4'),
                                __('website.testimonial_5')
                            ];
                        @endphp
                        
                        @foreach($testimonials as $index => $testimonial)
                            <button class="pagination-dot {{ $index === 1 ? 'active' : '' }}" onclick="goToTestimonial({{ $index }})" style="width: 12px; height: 12px; border-radius: 50%; border: none; background-color: {{ $index === 1 ? '#ffc107' : '#6c757d' }}; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'"></button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
let currentTestimonial = 1; // Start with second testimonial (index 1)
const testimonials = @json($testimonials);

function changeTestimonial(direction) {
    const testimonialText = document.getElementById('testimonial-text');
    const dots = document.querySelectorAll('.pagination-dot');
    
    // Add fade out animation
    testimonialText.style.opacity = '0';
    testimonialText.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        // Update testimonial
        currentTestimonial += direction;
        
        // Loop around
        if (currentTestimonial < 0) {
            currentTestimonial = testimonials.length - 1;
        } else if (currentTestimonial >= testimonials.length) {
            currentTestimonial = 0;
        }
        
        // Update content
        testimonialText.textContent = testimonials[currentTestimonial];
        
        // Update dots
        dots.forEach((dot, index) => {
            dot.classList.remove('active');
            dot.style.backgroundColor = index === currentTestimonial ? '#ffc107' : '#6c757d';
        });
        
        // Add fade in animation
        testimonialText.style.opacity = '1';
        testimonialText.style.transform = 'translateY(0)';
    }, 250);
}

function goToTestimonial(index) {
    const testimonialText = document.getElementById('testimonial-text');
    const dots = document.querySelectorAll('.pagination-dot');
    
    if (index === currentTestimonial) return;
    
    // Add fade out animation
    testimonialText.style.opacity = '0';
    testimonialText.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        // Update testimonial
        currentTestimonial = index;
        
        // Update content
        testimonialText.textContent = testimonials[currentTestimonial];
        
        // Update dots
        dots.forEach((dot, dotIndex) => {
            dot.classList.remove('active');
            dot.style.backgroundColor = dotIndex === currentTestimonial ? '#ffc107' : '#6c757d';
        });
        
        // Add fade in animation
        testimonialText.style.opacity = '1';
        testimonialText.style.transform = 'translateY(0)';
    }, 250);
}

// Auto-rotate testimonials every 5 seconds
setInterval(() => {
    changeTestimonial(1);
}, 5000);

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const testimonialText = document.getElementById('testimonial-text');
    testimonialText.style.transition = 'all 0.5s ease';
});
</script>

<style>
/* Testimonial Animations */
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

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Section animations */
.testimonial-container {
    animation: fadeInUp 0.8s ease-out;
}

.testimonial-card {
    animation: slideInLeft 1s ease-out;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
}

/* Quote animations */
.testimonial-card .position-absolute:first-child {
    animation: slideInLeft 1.2s ease-out 0.3s both;
}

.testimonial-card .position-absolute:last-child {
    animation: slideInRight 1.2s ease-out 0.3s both;
}

/* Text animation */
.testimonial-text {
    animation: fadeInUp 1s ease-out 0.5s both;
}

/* Navigation arrows */
.testimonial-nav {
    opacity: 0.7;
}

.testimonial-nav:hover {
    opacity: 1;
    color: #e0a800 !important;
}

/* Pagination dots */
.pagination-dot {
    cursor: pointer;
}

.pagination-dot:hover {
    background-color: #ffc107 !important;
}

.pagination-dot.active {
    transform: scale(1.2);
}

/* Responsive design */
@media (max-width: 768px) {
    .testimonial-card {
        padding: 2rem !important;
        margin: 0 1rem;
    }
    
    .testimonial-nav {
        display: none;
    }
    
    .testimonial-card .position-absolute:first-child,
    .testimonial-card .position-absolute:last-child {
        font-size: 3rem !important;
    }
}

/* Smooth transitions */
.testimonial-text {
    transition: all 0.5s ease;
}

.pagination-dot {
    transition: all 0.3s ease;
}

/* Card entrance animation */
.testimonial-card {
    animation: fadeInUp 0.8s ease-out;
}

/* Quote mark animations */
@keyframes quoteAppear {
    from {
        opacity: 0;
        transform: scale(0);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.testimonial-card .position-absolute:first-child,
.testimonial-card .position-absolute:last-child {
    animation: quoteAppear 0.6s ease-out 0.8s both;
}
</style>
