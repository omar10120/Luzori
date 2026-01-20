<!-- Footer Component -->
<footer class="py-5" style="background-color: #1a1a1a;" id="footer">
    <div class="container">
        <div class="row g-4">
            <!-- Company Information -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-4">
                    <!-- Logo -->
                    <div class="d-flex align-items-center mb-3">
                        
                        
                            <img src="{{ asset('logo-white.svg') }}" alt="Luzori Logo" style="height: 50px; width: auto;" class="me-3">
                        
                    </div>
                    
                    <!-- Company Description -->
                    <p class="text-light mb-0 lh-lg">
                        {{ __('website.footer_description') }}
                    </p>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <div class="footer-section">
                    <h5 class="text-white fw-bold mb-4">{{ __('website.quick_links') }}</h5>
                    <ul class="list-unstyled ">
                        <li class="mb-2 ">
                            <a href="#home" class="text-light text-decoration-none footer-link" style="transition: all 0.3s ease;" onmouseover="this.style.color='#ffc107'; this.style.paddingLeft='5px'" onmouseout="this.style.color='#adb5bd'; this.style.paddingLeft='0'">
                                {{ __('website.home') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#about" class="text-light text-decoration-none footer-link" style="transition: all 0.3s ease;" onmouseover="this.style.color='#ffc107'; this.style.paddingLeft='5px'" onmouseout="this.style.color='#adb5bd'; this.style.paddingLeft='0'">
                                {{ __('website.about') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#services" class="text-light text-decoration-none footer-link" style="transition: all 0.3s ease;" onmouseover="this.style.color='#ffc107'; this.style.paddingLeft='5px'" onmouseout="this.style.color='#adb5bd'; this.style.paddingLeft='0'">
                                {{ __('website.services') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#contact" class="text-light text-decoration-none footer-link" style="transition: all 0.3s ease;" onmouseover="this.style.color='#ffc107'; this.style.paddingLeft='5px'" onmouseout="this.style.color='#adb5bd'; this.style.paddingLeft='0'">
                                {{ __('website.contact') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Services -->
         -->
            
            <!-- Social Media -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-section">
                    <h5 class="text-white fw-bold mb-3">{{ __('website.follow_us') }}</h5>
                    <div class="social-links d-flex flex-wrap align-items-center gap-2">
                        @php
                            $socialLinks = [
                                [
                                    'name' => 'Facebook',
                                    'icon' => 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z',
                                    'color' => '#1877F2',
                                    'url' => '#'
                                ],
                                [
                                    'name' => 'LinkedIn',
                                    'icon' => 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z',
                                    'color' => '#0077B5',
                                    'url' => '#'
                                ],
                                [
                                    'name' => 'Twitter',
                                    'icon' => 'M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z',
                                    'color' => '#1DA1F2',
                                    'url' => '#'
                                ],
                                [
                                    'name' => 'Instagram',
                                    'icon' => 'M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987s11.987-5.367 11.987-11.987C24.014 5.367 18.647.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.708 13.744 3.708 12.447s.49-2.448 1.297-3.323c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.807.875 1.297 2.026 1.297 3.323s-.49 2.448-1.297 3.323c-.875.807-2.026 1.297-3.323 1.297zm7.83-9.281c-.49 0-.98-.2-1.297-.49-.317-.29-.49-.68-.49-1.17 0-.49.173-.98.49-1.297.317-.317.807-.49 1.297-.49s.98.173 1.297.49c.317.317.49.807.49 1.297 0 .49-.173.98-.49 1.297-.317.29-.807.49-1.297.49z',
                                    'color' => '#E4405F',
                                    'url' => '#'
                                ],
                                [
                                    'name' => 'TikTok',
                                    'icon' => 'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z',
                                    'color' => '#000000',
                                    'url' => '#'
                                ]
                            ];
                        @endphp
                        
                        @foreach($socialLinks as $social)
                            <div class="social-item">
                                <a href="{{ $social['url'] }}" class="social-link d-flex align-items-center justify-content-center text-decoration-none rounded-circle" style="background-color: {{ $social['color'] }}; transition: all 0.3s ease; width: 50px; height: 50px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 3px 10px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                                    <svg width="20" height="20" fill="white" viewBox="0 0 24 24">
                                        <path d="{{ $social['icon'] }}"/>
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright Section -->
        <hr class="my-5" style="border-color: #495057;">
        <div class="row">
            <div class="col-12 text-center">
                <p class="text-light mb-0 fs-6">
                    {{ __('website.copyright') }}
                </p>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Animations */
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
.footer-brand {
    animation: slideInLeft 0.8s ease-out;
}

.footer-section:nth-child(2) {
    animation: fadeInUp 0.8s ease-out 0.1s both;
}

.footer-section:nth-child(3) {
    animation: fadeInUp 0.8s ease-out 0.2s both;
}

.footer-section:nth-child(4) {
    animation: slideInRight 0.8s ease-out 0.3s both;
}

/* Logo animation */
.footer-brand img {
    transition: all 0.3s ease;
}

.footer-brand:hover img {
    transform: scale(1.05);
    filter: brightness(1.1);
}

/* Footer links animation */
.footer-link {
    position: relative;
    display: inline-block;
}

.footer-link::before {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background-color: #ffc107;
    transition: width 0.3s ease;
}

.footer-link:hover::before {
    width: 100%;
}

/* Social links animation */
.social-item {
    animation: fadeInUp 0.6s ease-out both;
}

.social-item:nth-child(1) { animation-delay: 0.1s; }
.social-item:nth-child(2) { animation-delay: 0.2s; }
.social-item:nth-child(3) { animation-delay: 0.3s; }
.social-item:nth-child(4) { animation-delay: 0.4s; }
.social-item:nth-child(5) { animation-delay: 0.5s; }

.social-link {
    position: relative;
    overflow: hidden;
}

.social-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transition: left 0.5s ease;
}

.social-link:hover::before {
    left: 100%;
}

/* Responsive design */
@media (max-width: 768px) {
    .footer-section {
        margin-bottom: 2rem;
    }
    
    .social-item {
        margin-bottom: 0.75rem;
    }
    
    .social-link {
        padding: 0.5rem !important;
    }
    
    .social-link svg {
        width: 16px;
        height: 16px;
    }
}

/* Smooth transitions */
.footer-brand,
.footer-section,
.footer-link,
.social-link {
    transition: all 0.3s ease;
}

/* Hover effects */
.footer-section:hover h5 {
    color: #ffc107;
}

/* Copyright animation */
hr {
    animation: fadeInUp 0.8s ease-out 0.6s both;
}

.text-center p {
    animation: fadeInUp 0.8s ease-out 0.7s both;
}
</style>
