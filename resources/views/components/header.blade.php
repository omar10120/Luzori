<!-- Header Component -->
@php $isRtl = app()->getLocale() == 'ar'; @endphp
<header class="bg-dark text-white sticky-top shadow-lg" style="z-index: 1050; transition: all 0.3s ease-in-out;">
    <!-- Top Bar -->
    <div class="bg-secondary py-2" style="transition: all 0.3s ease-in-out;">
        <div class="container-fluid">
            <!-- Desktop Layout -->
            <div class="row align-items-center d-none d-md-flex">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center me-4" style="transition: all 0.3s ease-in-out;">
                            <svg class="text-warning me-2" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="transition: all 0.3s ease-in-out;">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                            <span class="text-light small" style="transition: all 0.3s ease-in-out;">support@etechnocode.com</span>
                        </div>
                        <div class="d-flex align-items-center" style="transition: all 0.3s ease-in-out;">
                            <svg class="text-warning me-2" width="16" height="16" fill="currentColor" viewBox="0 0 20 20" style="transition: all 0.3s ease-in-out;">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                            </svg>
                            <span class="text-light small" style="transition: all 0.3s ease-in-out;">+(971) 50-314-0232</span>
                        </div>
                    </div>
                </div>
                
                <!-- Language Switcher & Social Media Icons -->
                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-center">
                        <!-- Language Switcher -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-warning btn-sm dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(app()->getLocale() == 'ar')
                                    🇦🇪 العربية
                                @else
                                    🇺🇸 English
                                @endif
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('locale.switch', 'en') }}">
                                        🇺🇸 English
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('locale.switch', 'ar') }}">
                                        🇦🇪 العربية
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <!-- <a href="#" class="text-light me-3" style="transition: all 0.3s ease-in-out; transform: scale(1);" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="transition: all 0.3s ease-in-out;">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-light me-3" style="transition: all 0.3s ease-in-out; transform: scale(1);" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="transition: all 0.3s ease-in-out;">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-light me-3" style="transition: all 0.3s ease-in-out; transform: scale(1);" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="transition: all 0.3s ease-in-out;">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-light me-3" style="transition: all 0.3s ease-in-out; transform: scale(1);" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="transition: all 0.3s ease-in-out;">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a> -->
                        <!-- <a href="#" class="text-light" style="transition: all 0.3s ease-in-out; transform: scale(1);" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="transition: all 0.3s ease-in-out;">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001 12.017.001z"/>
                            </svg>
                        </a> -->
                    </div>
                </div>
            </div>

            <!-- Mobile Layout -->
            <div class="d-md-none">
                <div class="row">
                    <!-- Contact Info & Language -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <!-- Contact Info -->
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center me-2">
                                    <svg class="text-warning me-1" width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                    </svg>
                                    <span class="text-light" style="font-size: 0.7rem;">support@etechnocode.com</span>
                                </div>
                                <div class="d-flex align-items-center me-2">
                                    <svg class="text-warning me-1" width="10" height="10" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                    </svg>
                                    <span class="text-light" style="font-size: 0.5rem;">+(971) 50-314-0232</span>
                                </div>
                            </div>
                            
                            <!-- Language Switcher -->
                            <div class="dropdown">
                                <button class="btn btn-outline-warning btn-sm dropdown-toggle" type="button" id="mobileLanguageDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.7rem; padding: 0.2rem 0.4rem;">
                                    @if(app()->getLocale() == 'ar')
                                        🇦🇪 العربية
                                    @else
                                        🇺🇸 English
                                    @endif
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="mobileLanguageDropdown">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('locale.switch', 'en') }}">
                                            🇺🇸 English
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('locale.switch', 'ar') }}">
                                            🇦🇪 العربية
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media Icons -->
               
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-md bg-dark py-2" style="transition: all 0.3s ease-in-out;">
        <div class="container-fluid {{ $isRtl ? 'flex-row-reverse' : '' }}">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="#" style="transition: all 0.3s ease-in-out;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="{{ asset('logo-white.svg') }}" alt="Luzori Logo" style="height: 32px; width: auto;" class="{{ $isRtl ? 'ms-2' : 'me-2' }}">
                <!-- <span class="text-white fw-bold fs-4" style="transition: all 0.3s ease-in-out;">
                    Luzori
                    <span class="text-warning" style="transition: all 0.3s ease-in-out;">.</span>
                </span> -->
            </a>

            <!-- Desktop Navigation Links -->
            <div class="d-none d-md-flex align-items-center">
                <ul class="navbar-nav {{ $isRtl ? 'ms-auto' : 'me-auto' }} mb-2 mb-lg-0">
                    <li class="nav-item me-3">
                        <a class="nav-link text-warning fw-medium position-relative" href="#home" style="transition: all 0.3s ease-in-out; font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                            {{ __('website.home') }}
                            <div class="nav-underline position-absolute bottom-0 start-0 bg-warning" style="height: 2px; width: 100%; transition: all 0.3s ease-in-out;"></div>
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link text-light position-relative" href="#about" style="transition: all 0.3s ease-in-out; font-size: 0.9rem; padding: 0.5rem 0.75rem;" onmouseover="this.style.color='#ffc107'; this.querySelector('.nav-underline').style.width='100%'" onmouseout="this.style.color='#f8f9fa'; this.querySelector('.nav-underline').style.width='0%'">
                            {{ __('website.about') }}
                            <div class="nav-underline position-absolute bottom-0 start-0 bg-warning" style="height: 2px; width: 0%; transition: all 0.3s ease-in-out;"></div>
                        </a>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link text-light position-relative" href="#services" style="transition: all 0.3s ease-in-out; font-size: 0.9rem; padding: 0.5rem 0.75rem;" onmouseover="this.style.color='#ffc107'; this.querySelector('.nav-underline').style.width='100%'" onmouseout="this.style.color='#f8f9fa'; this.querySelector('.nav-underline').style.width='0%'">
                            {{ __('website.services') }}
                            <div class="nav-underline position-absolute bottom-0 start-0 bg-warning" style="height: 2px; width: 0%; transition: all 0.3s ease-in-out;"></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light position-relative" href="#contact" style="transition: all 0.3s ease-in-out; font-size: 0.9rem; padding: 0.5rem 0.75rem;" onmouseover="this.style.color='#ffc107'; this.querySelector('.nav-underline').style.width='100%'" onmouseout="this.style.color='#f8f9fa'; this.querySelector('.nav-underline').style.width='0%'">
                            {{ __('website.contact') }}
                            <div class="nav-underline position-absolute bottom-0 start-0 bg-warning" style="height: 2px; width: 0%; transition: all 0.3s ease-in-out;"></div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- CTA Button -->
            <div class="d-none d-md-block">
                <a href="{{ route('center_user.login') }}" class="btn btn-warning px-3 py-1 fw-medium me-2" style="transition: all 0.3s ease-in-out; transform: scale(1); font-size: 0.85rem;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                    {{ __('website.login') }}
                </a>
                <a href="#footer" class="btn btn-outline-warning px-3 py-1 fw-medium" style="transition: all 0.3s ease-in-out; transform: scale(1); font-size: 0.85rem;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                    {{ __('website.get_in_touch') }}
                </a>
            </div>

            <!-- Mobile Right Controls: Login + Toggler -->
            <div class="d-flex d-md-none align-items-center ms-auto gap-2 {{ $isRtl ? 'flex-row-reverse' : '' }}">
                <a href="{{ route('center_user.login') }}" class="btn btn-warning px-3 py-1 fw-medium" style="transition: all 0.3s ease-in-out; transform: scale(1); font-size: 0.85rem;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                    {{ __('website.login') }}
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation" style="transition: all 0.3s ease-in-out;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <svg class="text-light" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition: all 0.3s ease-in-out;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="collapse d-md-none" id="mobileMenu" style="transition: all 0.3s ease-in-out;">
        <div class="bg-secondary border-top" style="transition: all 0.3s ease-in-out;">
            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-12">
                        <ul class="navbar-nav">
                            <li class="nav-item mb-3">
                                <a class="nav-link text-warning fw-medium" href="#home" style="transition: all 0.3s ease-in-out;" onmouseover="this.style.paddingLeft='20px'" onmouseout="this.style.paddingLeft='0px'">{{ __('website.home') }}</a>
                            </li>
                            <li class="nav-item mb-3">
                                <a class="nav-link text-light" href="#about" style="transition: all 0.3s ease-in-out;" onmouseover="this.style.color='#ffc107'; this.style.paddingLeft='20px'" onmouseout="this.style.color='#f8f9fa'; this.style.paddingLeft='0px'">{{ __('website.about') }}</a>
                            </li>
                            <li class="nav-item mb-3">
                                <a class="nav-link text-light" href="#services" style="transition: all 0.3s ease-in-out;" onmouseover="this.style.color='#ffc107'; this.style.paddingLeft='20px'" onmouseout="this.style.color='#f8f9fa'; this.style.paddingLeft='0px'">{{ __('website.services') }}</a>
                            </li>
                            <li class="nav-item mb-4">
                                <a class="nav-link text-light" href="#contact" style="transition: all 0.3s ease-in-out;" onmouseover="this.style.color='#ffc107'; this.style.paddingLeft='20px'" onmouseout="this.style.color='#f8f9fa'; this.style.paddingLeft='0px'">{{ __('website.contact') }}</a>
                            </li>
                        </ul>
                        <div class="border-top pt-3">
                            <a href="{{ route('center_user.login') }}" class="btn btn-warning w-100 fw-medium mb-2 py-2" style="transition: all 0.3s ease-in-out; transform: scale(1); font-size: 0.85rem;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            {{ __('website.login') }}
                            </a>
                            <a href="#" class="btn btn-outline-warning w-100 fw-medium py-2" style="transition: all 0.3s ease-in-out; transform: scale(1); font-size: 0.85rem;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            {{ __('website.get_in_touch') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
/* Enhanced Bootstrap Animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Enhanced hover effects */
.nav-link:hover {
    animation: fadeIn 0.3s ease-in-out;
}

.btn:hover {
    animation: pulse 0.6s ease-in-out;
}

/* Mobile menu animation */
.collapse.show {
    animation: slideDown 0.3s ease-in-out;
}

/* Logo animation */
.navbar-brand:hover .position-absolute {
    animation: pulse 0.6s ease-in-out;
}

/* Social icons bounce effect */
.social-icon {
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.social-icon:hover {
    transform: scale(1.2) rotate(5deg);
}
</style>

<script>
// Active navigation highlighting
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
    
    // Function to update active nav link
    function updateActiveNavLink() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('text-warning');
            link.classList.add('text-light');
            
            // Remove active underline
            const underline = link.querySelector('.nav-underline');
            if (underline) {
                underline.style.width = '0%';
            }
            
            // Check if this link corresponds to current section
            if (link.getAttribute('href') === '#' + current) {
                link.classList.remove('text-light');
                link.classList.add('text-warning');
                
                // Add active underline
                if (underline) {
                    underline.style.width = '100%';
                }
            }
        });
    }
    
    // Update on scroll
    window.addEventListener('scroll', updateActiveNavLink);
    
    // Update on page load
    updateActiveNavLink();
    
    // Smooth scroll for navigation links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offsetTop = targetSection.offsetTop - 100; // Account for header height
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Close mobile menu when clicking outside or on a menu item
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileToggler = document.querySelector('[data-bs-target="#mobileMenu"]');
    if (mobileMenu && mobileToggler) {
        // Click outside to close
        document.addEventListener('click', function(event) {
            const isOpen = mobileMenu.classList.contains('show');
            if (!isOpen) return;
            const clickedInsideMenu = mobileMenu.contains(event.target);
            const clickedToggler = mobileToggler.contains(event.target);
            if (clickedInsideMenu || clickedToggler) return;
            const bs = window.bootstrap;
            if (bs && bs.Collapse) {
                bs.Collapse.getOrCreateInstance(mobileMenu).hide();
            } else {
                mobileMenu.classList.remove('show');
            }
        });

        // Click on any link/button inside menu to close
        mobileMenu.querySelectorAll('a, button').forEach(function(el) {
            el.addEventListener('click', function() {
                const bs = window.bootstrap;
                if (bs && bs.Collapse) {
                    bs.Collapse.getOrCreateInstance(mobileMenu).hide();
                } else {
                    mobileMenu.classList.remove('show');
                }
            });
        });
    }
});
</script>