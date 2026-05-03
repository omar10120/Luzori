@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('center_login.login_page'))

@section('vendor-style')
    @vite('resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css')
@endsection

@section('page-style')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');
        
        :root {
            --brand-dark: #225D5C;
            --brand-light: #F2E8DC;
            --brand-peach: #FFD6A8;
            --brand-darker: #1D4E4D;
            --text-dark: #22403F;
            --text-muted: #8B9A9A;
            --border-color: #E9E2D8;
        }

        .register-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            background-color: white;
            font-family: 'cairo', sans-serif;
            position: relative;
            flex-direction: column;
        }

        @media (min-width: 992px) {
            .register-wrapper {
                flex-direction: row;
            }
        }

        .register-lang-fixed {
            position: fixed;
            top: 0.75rem;
            right: 0.75rem;
            z-index: 1080;
        }

        [dir="rtl"] .register-lang-fixed {
            right: auto;
            left: 0.75rem;
        }

        .lang-switcher-register .register-lang-btn {
            border: 1px solid var(--brand-dark);
            color: var(--brand-dark);
            background: #fff;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem 0.85rem;
        }

        .lang-switcher-register .register-lang-btn:hover,
        .lang-switcher-register .register-lang-btn:focus {
            background: var(--brand-dark);
            color: var(--brand-peach);
            border-color: var(--brand-dark);
        }

        /* Left panel (desktop) */
        .register-left {
            background-image: url("{{ asset('fogleft.png') }}");
            background-size: cover;
            background-position: right center;
            color: var(--brand-peach);
            display: none;
            position: relative;
        }

        [dir="rtl"] .register-left {
            background-image: url("{{ asset('fogright.png') }}");
            background-position: left center;
        }

        @media (min-width: 992px) {
            .register-left {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 50%;
            }
        }

        .register-left-content {
            text-align: center;
            padding: 2rem;
            max-width: 450px;
            z-index: 2;
        }

        .register-left h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--brand-peach);
        }

        .register-left .logo-circle {
            width: 100px;
            height: 100px;
            background: var(--brand-peach);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .register-left .logo-circle img {
            width: 70px;
            height: 70px;
        }

        .register-left p {
            font-size: 0.95rem;
            line-height: 1.8;
            color: var(--brand-peach);
            margin-bottom: 2rem;
        }

        .register-left .bottom-links {
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Right panel (form) */
        .register-right {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background: white;
        }

        @media (min-width: 992px) {
            .register-right {
                width: 50%;
                background: white;
            }
        }

        .register-card {
            background: #FFFBF7;
            border-radius: 1.5rem;
            border: 1px solid var(--border-color);
            padding: 2rem;
            width: 100%;
            max-width: 780px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            margin-top: -45px;
        }

        @media (min-width: 768px) {
            .register-card {
                padding: 2.5rem;
                border: none;
                background: white;
                box-shadow: none;
            }
        }

        @media (max-width: 768px) {
            .register-card {
                height: auto;
                overflow: visible;
            }
        }

        .register-title {
            color: var(--brand-dark);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        /* Form styles */
        .form-label-bs {
            color: var(--brand-dark);
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .input-group-icon {
            position: relative;
        }

        .input-group-icon .icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: var(--brand-dark);
            opacity: 0.7;
            z-index: 5;
        }

        .input-group-icon .icon-inline-start {
            inset-inline-start: 12px;
            inset-inline-end: auto;
        }

        .input-group-icon .icon-inline-end {
            inset-inline-end: 12px;
            inset-inline-start: auto;
        }

        .form-control-custom {
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            background-color: var(--brand-light);
            color: var(--text-dark);
            font-size: 0.95rem;
            padding: 0.65rem 1rem;
            width: 100%;
            transition: all 0.2s;
        }

        .form-control-custom:focus {
            border-color: var(--brand-dark);
            box-shadow: 0 0 0 0.2rem rgba(34,93,92,0.2);
            background-color: white;
        }

        @media (min-width: 768px) {
            .form-control-custom {
                background-color: rgba(255,214,168,0.3);
            }
        }

        .form-control-custom.pad-inline-start {
            padding-inline-start: 2.35rem;
        }

        .form-control-custom.pad-inline-end {
            padding-inline-end: 2.35rem;
        }

        .form-check-input {
            background-color: white;
            border-color: var(--brand-dark);
        }

        .form-check-input:checked {
            background-color: var(--brand-dark) !important;
            border-color: var(--brand-dark);
        }

        .btn-register {
            background-color: var(--brand-dark) !important;
            color: var(--brand-peach) !important;
            font-weight: 700 !important;
            border-radius: 50rem !important;
            padding: 0.65rem 3rem !important;
            transition: 0.2s !important;
            border: none !important;
        }

        .btn-register:hover {
            background-color: var(--brand-darker);
            color: var(--brand-peach);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .alert-status {
            font-size: 0.85rem;
            border-radius: 0.75rem;
        }

        /* Mobile top banner */
        .mobile-welcome {
            background: var(--brand-dark) !important;
            border-radius: 0 0 34px 34px !important;
            padding: 1rem 1rem 1.5rem !important;
            text-align: center;
            display: block !important;
        }

        .mobile-welcome h2 {
            color: var(--brand-peach) !important;
        }

        @media (min-width: 992px) {
            .mobile-welcome {
                display: none !important;
            }
        }

        .mobile-welcome .logo-sm {
            width: 50px !important;
            height: 50px !important;
            background: var(--brand-peach) !important;
            border-radius: 50% !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin-bottom: 0.5rem !important;
        }

        .mobile-welcome .logo-sm img {
            width: 36px;
            height: 36px;
        }

        .form-control-custom::placeholder {
            color: var(--brand-dark);
            opacity: 0.5;
        }

        .form-control-custom:-ms-input-placeholder {
            opacity: 0.5;
        }

        .form-control-custom::-moz-placeholder {
            opacity: 0.5;
        }
        input[type="checkbox"] , input[type="checkbox"]:checked {
            background-color: var(--brand-dark);
            border-color: var(--brand-dark);
        }
    </style>
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@endsection

@section('page-script')
    @vite('resources/assets/js/pages-auth.js')
    <script>
        $(document).ready(function() {
            const domainParam = @json(request()->query('domain'));
            const postUrl = "{{ route('center_user.login') }}" + (domainParam ? ("?domain=" + encodeURIComponent(domainParam)) : "");
            
            // Debug outputs (kept for troubleshooting)
            $("#debug-post-url").text(postUrl);
            $("#debug-domain").text(domainParam || '(none)');
            $("#debug-host").text(window.location.host);

            // Password toggle
            $('.toggle-password').click(function() {
                let input = $(this).siblings('input');
                let icon = $(this).find('i');
                if (input.attr('type') == 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                }
            });

            $("#frmLogin").on("submit", function(event) {
                event.preventDefault();

                $.ajax({
                    url: postUrl,
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    headers: domainParam ? { 'domain': domainParam } : {},
                    beforeSend: function() {
                        $('#listError').empty();
                        $("#alertError").hide();
                        $(".submitFrom span").html('{{ __('center_login.logining') }}');
                        $('.submitFrom').prop('disabled', true);
                    },
                    success: function(response, textStatus, xhr) {
                        console.log('Login response:', response);
                        $('#debug-response').text(JSON.stringify(response));
                        
                        if (xhr.status == 200) {
                            if (response.data && response.data.redirect_url) {
                                console.log('Redirecting to:', response.data.redirect_url);
                                window.location.href = response.data.redirect_url;
                            } else {
                                console.log('No redirect_url found, using default redirect');
                                window.location.href = "{{ route('center_user.cp') }}" + (domainParam ? ("?domain=" + encodeURIComponent(domainParam)) : "");
                            }
                        } else {
                            $("#alertError").show();
                            $('#listError').html(response.message || 'Unexpected response');
                        }

                        $("html, body").animate({ scrollTop: 0 }, { duration: 1500 });
                        $(".submitFrom span").html('{{ __('center_login.login') }}');
                        $('.submitFrom').prop('disabled', false);
                    },
                    error: function(response) {
                        $("#alertError").show();
                        var errors = response.responseJSON && response.responseJSON.errors;
                        if (errors) {
                            for (var error in errors) {
                                var ul = document.getElementById("listError");
                                var li = document.createElement("li");
                                li.appendChild(document.createTextNode(errors[error]));
                                ul.appendChild(li);
                            }
                        } else if (response.responseJSON && response.responseJSON.message) {
                            $('#listError').html(response.responseJSON.message);
                        } else {
                            $('#listError').html('Login failed with status ' + response.status);
                        }

                        $("html, body").animate({ scrollTop: 0 }, { duration: 1500 });
                        $(".submitFrom span").html('{{ __('center_login.login') }}');
                        $('.submitFrom').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
<div class="register-wrapper" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="register-lang-fixed">
        @include('components.lang_switcher', ['variant' => 'register'])
    </div>

    <!-- Mobile Welcome Banner -->
    <div class="mobile-welcome w-100">
        <div class="logo-sm">
            <img src="{{ asset('logo_test.svg') }}" alt="{{ __('center_register.meta_title') }}">
        </div>
        <h2 class="h5 fw-bold mt-2">{{ __('center_login.welcome_back') ?? 'Welcome Back' }}</h2>
        <p class="small mb-0" style="color:#F2E8DC;">{{ __('center_login.signin_prompt_mobile') ?? 'Login to your account' }}</p>
    </div>

    <!-- Left panel (desktop) -->
    <div class="register-left">
        <div class="register-left-content">
            <h2>{{ __('center_login.welcome_left_title') ?? 'Welcome Back!' }}</h2>
            <div class="logo-circle">
                <img src="{{ asset('logo_test.svg') }}" alt="{{ __('center_register.meta_title') }}">
            </div>
            <p>{{ __('center_login.left_panel_text') ?? 'Access your center dashboard, manage classes, and track your business growth.' }}</p>
            <div class="bottom-links fw-bold">
                <span>{{ __('center_login.contact_support') ?? 'Contact Support' }}</span> 
                <span class="mx-2">|</span> 
                <span>{{ __('center_login.learn_more') ?? 'Learn More' }}</span>
            </div>
        </div>
    </div>

    <!-- Right panel (form) -->
    <div class="register-right">
        <div class="register-card">
            <h1 class="register-title">{{ __('center_login.signin_title') ?? 'Sign In' }}</h1>

            <!-- Alert -->
            <div id="alertError" class="alert alert-danger alert-status d-none" role="alert">
                <div id="listError"></div>
            </div>

            <form id="frmLogin" class="mb-3" enctype="multipart/form-data" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <!-- Email -->
                        <label class="form-label-bs">{{ __('field.email') }} <span class="text-danger">*</span></label>
                        <div class="input-group-icon mb-1">
                            <i class="ti ti-mail icon icon-inline-start"></i>
                            <input type="email" name="email" id="email" class="form-control-custom pad-inline-start" 
                                   placeholder="{{ __('general.enter') . ' ' . __('field.email') }}" 
                                   autofocus required>
                        </div>
                    </div>

                    <div class="col-12">
                        <!-- Password -->
                        <div class="d-flex justify-content-between">
                            <label class="form-label-bs">{{ __('field.password') }} <span class="text-danger">*</span></label>
                            <!-- <a href="{{ url('auth/forgot-password-cover') }}" class="small text-muted">
                                {{ __('center_login.forgot_password') ?? 'Forgot Password?' }}
                            </a> -->
                        </div>
                        <div class="input-group-icon mb-1">
                            <i class="ti ti-lock icon icon-inline-start"></i>
                            <input type="password" name="password" id="password" class="form-control-custom pad-inline-start pad-inline-end" 
                                   placeholder="{{ __('general.enter') . ' ' . __('field.password') }}" required>
                            <span class="icon icon-inline-end toggle-password" style="cursor:pointer;">
                                <i class="ti ti-eye-off"></i>
                            </span>
                        </div>
                    </div>

                    <div class="col-12">
                        <!-- Remember Me -->
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember">
                            <label class="form-check-label text-muted small" for="remember-me">
                                {{ __('center_login.remember_me') }}
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-register submitFrom">
                        <span>{{ __('center_login.login') }}</span>
                    </button>
                </div>
            </form>

            <!-- Debug Panel (hidden in production, kept for troubleshooting) -->
            <div class="mt-3 small text-muted" style="display: none;">
                <hr>
                <strong>Debug:</strong><br>
                POST URL: <span id="debug-post-url"></span><br>
                Domain param: <span id="debug-domain"></span><br>
                Host: <span id="debug-host"></span><br>
                Response: <span id="debug-response"></span>
            </div>
        </div>
    </div>
</div>
@endsection