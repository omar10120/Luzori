<!-- About Component -->
<section class="py-5" style="background-color: #212529;" id="about">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- About Header -->
                <div class="text-center mb-5">
                    <h6 class="text-warning text-uppercase fw-bold mb-3" style="letter-spacing: 2px;">{{ __('website.about') }}</h6>
                    <!-- <h2 class="display-8 fw-bold text-white mb-4">
                        {!! __('website.about_title', ['highlight' => '<span class="text-warning ms-2">*</span>']) !!}
                    </h2> -->
                </div>
                
                <!-- About Content -->
                <div class="row mb-5">
                    <div class="col-lg-6">
                        <div class="about-text-content">
                            <!-- Main Description -->
                            <div class="mb-4">
                                <h4 class="text-warning fw-bold mb-3">{{ __('website.about_subtitle') }}</h4>
                                <p class="text-light lh-lg">
                                    {{ __('website.about_description') }}
                                </p>
                            </div>
                            
                            <!-- Core Values -->
                            <div class="core-values">
                                <h5 class="text-white fw-bold mb-4">{{ __('website.our_values') }}</h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="value-card d-flex align-items-center p-3 rounded-3" style="background-color: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107;">
                                            <div class="value-icon me-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 50px; background-color: rgba(255, 193, 7, 0.15); border-radius: 50%;">
                                                <img src="{{ asset('assets/icons/Employee management.svg') }}" alt="Employee Management" style="width: 28px; height: 28px; object-fit: contain;">
                                            </div>
                                            <div>
                                                <h6 class="text-white fw-bold mb-1">{{ __('website.expert_team') }}</h6>
                                                <p class="text-light mb-0 small">{{ __('website.expert_team_description') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="value-card d-flex align-items-center p-3 rounded-3" style="background-color: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107;">
                                            <div class="value-icon me-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 50px; background-color: rgba(255, 193, 7, 0.15); border-radius: 50%;">
                                                <img src="{{ asset('assets/icons/Reports and Analysis.svg') }}" alt="Reports and Analysis" style="width: 28px; height: 28px; object-fit: contain;">
                                            </div>
                                            <div>
                                                <h6 class="text-white fw-bold mb-1">{{ __('website.quality_delivery') }}</h6>
                                                <p class="text-light mb-0 small">{{ __('website.quality_delivery_description') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="value-card d-flex align-items-center p-3 rounded-3" style="background-color: rgba(255, 193, 7, 0.1); border-left: 4px solid #ffc107;">
                                            <div class="value-icon me-3 d-flex align-items-center justify-content-center" style="width: 65px; height: 50px; background-color: rgba(255, 193, 7, 0.15); border-radius: 50%;">
                                                <img src="{{ asset('assets/icons/Electronic wallet.svg') }}" alt="Electronic Wallet" style="width: 28px; height: 28px; object-fit: contain;">
                                            </div>
                                            <div>
                                                <h6 class="text-white fw-bold mb-1">{{ __('website.innovative_solutions') }}</h6>
                                                <p class="text-light mb-0 small">{{ __('website.innovative_solutions_description') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="about-visual-content">
                            <!-- Feature Highlights -->
                            <div class="feature-highlights ">
                                <h5 class="text-white fw-bold mb-6">{{ __('website.why_choose_us') }}</h5>
                                
                                <div class="feature-list">
                                    <div class="feature-item d-flex align-items-start mb-3">
                                        <div class="feature-bullet me-3 mt-1">
                                            <div class="bullet-point rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: #ffc107; ">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" class="text-dark">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="me-2">
                                            <h6 class="text-white fw-bold mb-1">{{ __('website.comprehensive_solution') }}</h6>
                                            <p class="text-light mb-0 small">{{ __('website.comprehensive_solution_desc') }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item d-flex align-items-start mb-3">
                                        <div class="feature-bullet me-3 mt-1">
                                            <div class="bullet-point rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: #ffc107;">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" class="text-dark">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="me-2">
                                            <h6 class="text-white fw-bold mb-1">{{ __('website.easy_to_use') }}</h6>
                                            <p class="text-light mb-0 small">{{ __('website.easy_to_use_desc') }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item d-flex align-items-start mb-3">
                                        <div class="feature-bullet me-3 mt-1">
                                            <div class="bullet-point rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: #ffc107;">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" class="text-dark">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="me-2">
                                            <h6 class="text-white fw-bold mb-1">{{ __('website.scalable_growth') }}</h6>
                                            <p class="text-light mb-0 small">{{ __('website.scalable_growth_desc') }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="feature-item d-flex align-items-start mb-3">
                                        <div class="feature-bullet me-3 mt-1">
                                            <div class="bullet-point rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; background-color: #ffc107;">
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24" class="text-dark">
                                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="me-2">
                                            <h6 class="text-white fw-bold mb-1">{{ __('website.timely_support') }}</h6>
                                            <p class="text-light mb-0 small">{{ __('website.timely_support_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="stats-card text-center p-4 rounded-3" style="background-color: #343a40; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <h3 class="display-9 fw-bold text-warning mb-2">550,000+</h3>
                            <p class="text-white fw-medium mb-0">{{ __('website.years_experience') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stats-card text-center p-4 rounded-3" style="background-color: #343a40; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <h3 class="display-9 fw-bold text-warning mb-2">75,000+</h3>
                            <p class="text-white fw-medium mb   -0">{{ __('website.projects_completed') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stats-card text-center p-4 rounded-3" style="background-color: #343a40; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <h3 class="display-9 fw-bold text-warning mb-2">80+</h3>
                            <p class="text-white fw-medium mb-0">{{ __('website.team_members') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="stats-card text-center p-4 rounded-3" style="background-color: #343a40; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <h3 class="display-9 fw-bold text-warning mb-2">4+ {{__('website.years')}}</h3>
                            <p class="text-white fw-medium mb-0">{{ __('website.happy_clients') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* About Section Animations */
.stats-card {
    border: 1px solid rgba(255, 193, 7, 0.1);
    position: relative;
    overflow: hidden;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 193, 7, 0.1), transparent);
    transition: left 0.5s ease;
}

.stats-card:hover::before {
    left: 100%;
}

.stats-card h3 {
    position: relative;
    z-index: 2;
}

.stats-card p {
    position: relative;
    z-index: 2;
}

/* Fade in animation for stats */
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

.stats-card {
    animation: fadeInUp 0.6s ease-out;
}

.stats-card:nth-child(1) { animation-delay: 0.1s; }
.stats-card:nth-child(2) { animation-delay: 0.2s; }
.stats-card:nth-child(3) { animation-delay: 0.3s; }
.stats-card:nth-child(4) { animation-delay: 0.4s; }

/* About content animations */
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

.about-text-content {
    animation: slideInLeft 0.8s ease-out;
}

.about-visual-content {
    animation: slideInRight 0.8s ease-out;
}

/* Value cards styling */
.value-card {
    transition: all 0.3s ease;
    border-radius: 8px;
}

.value-card:hover {
    background-color: rgba(255, 193, 7, 0.15) !important;
    transform: translateX(5px);
}

.value-icon {
    transition: all 0.3s ease;
}

.value-card:hover .value-icon {
    transform: scale(1.1);
}

/* Feature items styling */
.feature-item {
    transition: all 0.3s ease;
}

.feature-item:hover {
    transform: translateX(5px);
}

.bullet-point {
    transition: all 0.3s ease;
}

.feature-item:hover .bullet-point {
    transform: scale(1.1);
    background-color: #e0a800 !important;
}

/* Responsive design */
@media (max-width: 768px) {
    .about-text-content,
    .about-visual-content {
        margin-bottom: 2rem;
    }
    
    .value-card,
    .feature-item {
        margin-bottom: 1rem;
    }
}
</style>
