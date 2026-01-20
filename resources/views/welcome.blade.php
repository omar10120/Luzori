<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ session('direction', 'ltr') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $metaDescription = app()->getLocale() === 'ar'
                ? 'هو برنامج/نظام يساعد في تنظيم وتشغيل الأنشطة اليومية داخل الصالونات أو مراكز التجميل والعناية او مراكز بشكل عام، بهدف تحسين الكفاءة، تقليل الأخطاء، وزيادة رضا العملاء وتسهيل العمل الإداري ورفع كفاءة إدارة الوقت والموارد و توفير بيانات دقيقة لدعم اتخاذ القرار .'
                : 'A salon and beauty center management system to streamline daily operations, improve efficiency, reduce errors, delight customers, and provide accurate data for better decisions.';
            $ogImage = asset('assets/img/background.png');
        @endphp

        <meta name="description" content="{{ $metaDescription }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="Luzori - {{ __('website.hero_title') }}">
        <meta property="og:description" content="{{ $metaDescription }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="{{ app()->getLocale() }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Luzori - {{ __('website.hero_title') }}">
        <meta name="twitter:description" content="{{ $metaDescription }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
    <title>Luzori - {{ __('website.hero_title') }}</title>

        <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
        <style>
            body {
            font-family: 'Inter', sans-serif;
            background-color: #212529;
            color: white;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #212529;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #ffc107;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #e0a800;
        }
        
        .hero-section {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-image: url('{{ asset('assets/img/background.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(6px);
            transform: scale(1.05); /* avoid edge transparency after blur */
            z-index: 0;
        }

        .hero-section .container {
            position: relative;
            width: 90%;
            z-index: 1;
        }
        
        .btn-custom {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
            font-weight: 500;
            padding: 6px 20px;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            background-color: #e0a800;
            border-color: #e0a800;
            color: #212529;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        }
        
        .btn-outline-custom {
            border: 2px solid #ffc107;
            color: #ffc107;
            background-color: transparent;
            font-weight: 500;
            padding: 4px 20px;
            border-radius: 6px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .btn-outline-custom:hover {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
            transform: translateY(-2px);
        }
        
        /* Mobile spacing adjustments */
        @media (max-width: 768px) {
            .hero-section {
                min-height: 60vh;
                padding-bottom: 2rem;
            }
            
            #about {
                margin-top: -1rem;
            }
            }
        </style>
    </head>
<body>
    <!-- Header Component -->
    <x-header />
    
    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section id="home" class="hero-section d-flex align-items-center justify-content-center">
            <div class="container text-center">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h1 class="display-8 fw-bold text-white mb-4">
                            {!! __('website.hero_title', ['highlight' => '<span class="text-warning">Digitally+</span>']) !!}
                        </h1>
                        <p class="fs-6 text-light mb-5">
                            {{ __('website.hero_subtitle') }}
                        </p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="#contact" class="btn btn-custom">
                                {{ __('website.get_quote') }}
                            </a>
                            <a href="#about" class="btn btn-outline-custom">
                                {{ __('website.learn_more') }}
                            </a>
                        </div>
                                </div>
                            </div>
                        </div>
        </section>
        
        <!-- About Component -->
        <div id="about">
            <x-about />
                            </div>

        <!-- Services Component -->
        <div id="services">
            <x-services />
                        </div>

        <!-- Our Work Component -->
        <x-our-work />
        
        <!-- Why Customers Component -->
        <x-why-customers />
        
        <!-- FAQ Component -->
        <x-faq />
        
        
        <!-- Contact Component -->
        <div id="contact">
            <x-contact />
                    </div>

        <!-- Footer Component -->
        <div id="footer">
            <x-footer />
        </div>
        
        <!-- Additional sections will be added here -->
    </main>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
