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
                        @include('components.lang_switcher', ['variant' => 'header-desktop'])
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
                            
                            @include('components.lang_switcher', ['variant' => 'header-mobile'])
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
                   <a href="{{ route('center_user.signup') }}" class="btn btn-warning px-3 py-1 fw-medium me-2" style="transition: all 0.3s ease-in-out; transform: scale(1); font-size: 0.85rem;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                    {{ __('website.signup') }}
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
                            <a href="{{ route('center_user.signup') }}" class="btn btn-warning w-100 fw-medium mb-2 py-2" style="transition: all 0.3s ease-in-out; transform: scale(1); font-size: 0.85rem;" onmouseover="this.style.transform='scale(1.02)'; this.style.boxShadow='0 4px 15px rgba(255, 193, 7, 0.4)'" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none'">
                            {{ __('website.signup') }}
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