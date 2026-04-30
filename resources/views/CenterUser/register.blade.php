@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Register - Become Business')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css')
@endsection

@section('page-style')
    <style>
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
        }

        /* Left panel (desktop) */
        .register-left {
            background-image: url('/fogleft.png');
            background-size: cover;
            background-position: right center;
            color: var(--brand-peach);
            display: none;
            position: relative;
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
            max-width: 480px;
            z-index: 2;
        }

        .register-left h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
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
            color: #FFEED9;
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
            background: var(--brand-light);
       
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
            max-width: 550px;
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

        .input-group-icon .icon-left {
            left: 12px;
        }

        .input-group-icon .icon-right {
            right: 12px;
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

        .form-control-custom.ps-icon {
            padding-left: 2.2rem;
        }

        .form-control-custom.pe-icon {
            padding-right: 2.2rem;
        }

        /* Phone select + input */
        .phone-group {
            display: flex;
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .phone-group select {
            border: none;
            background-color: var(--brand-light);
            border-right: 1px solid #D8A7A0;
            border-radius: 0;
            padding: 0.5rem 0.5rem;
            font-weight: 600;
            color: var(--brand-dark);
            font-size: 0.85rem;
            cursor: pointer;
            flex: 0 0 auto;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23225D5C' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
            padding-right: 24px;
        }

        .phone-group input {
            border: none;
            flex: 1;
            border-radius: 0;
            background-color: var(--brand-light);
        }

        @media (min-width: 768px) {
            .phone-group select,
            .phone-group input {
                background-color: rgba(255,214,168,0.3);
            }
        }

        /* File upload */
        .logo-upload-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 2px dashed var(--border-color);
            background: var(--brand-light);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .logo-upload-circle img.preview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            border-radius: 50%;
        }

        .logo-upload-circle input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-box {
            border: 1px dashed var(--border-color);
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            position: relative;
            background: var(--brand-light);
        }

        @media (min-width: 768px) {
            .file-upload-box {
                background: rgba(255,214,168,0.3);
            }
        }

        .file-upload-box input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            cursor: pointer;
        }

        .btn-register {
            background-color: var(--brand-dark) !important;
            color: var(--brand-peach) !important;
            
            font-weight: 700  !important;;
            border-radius: 50rem  !important;;
            padding: 0.65rem 3rem  !important;;
            transition: 0.2s  !important;;
            border: none  !important;;
        }

        .btn-register:hover {
            background-color: var(--brand-darker);
            color: var(--brand-peach);
        }

        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .google-btn {
            height: 45px;
            border-radius: 50rem;
            background: var(--brand-light);
            border: none;
            color: var(--brand-dark);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.2s;
        }

        .google-btn:hover {
            background: #e9dfd0;
        }

        .alert-status {
            font-size: 0.85rem;
            border-radius: 0.75rem;
        }

        /* Mobile top banner */
        .mobile-welcome {
            background: var(--brand-dark) !important;
            color: var(--brand-peach) !important;
            border-radius: 0 0 34px 34px !important;
            padding: 1rem 1rem 1.5rem !important;
            text-align: center;
            display: block  !important;
        }

        @media (min-width: 992px) {
            .mobile-welcome {
                display: none !important;
            }
        }

        .mobile-welcome .logo-sm {
            width: 50px  !important;; 
            height: 50px  !important;;
            background: var(--brand-peach)  !important;;
            border-radius: 50%  !important;;
            display: inline-flex  !important;;
            align-items: center  !important;;
            justify-content: center  !important;;
            margin-bottom: 0.5rem  !important;;
        }

        .mobile-welcome .logo-sm img {
            width: 36px;
            height: 36px;
        }
        /* EDIT */
        input[type='checkbox'] {
            background-color:white;
            border-color:var(--brand-dark);
            
        }
        input[type='checkbox']:checked{
            background-color: var(--text-muted) !important;
        }
        select , option{
            background-color: var(--brand-light) !important;
            color : var(--text-muted) !important;
        }
        .register-wrapper {
            flex-direction: column;        /* stack children vertically by default */
        }

        @media (min-width: 992px) {
            .register-wrapper {
                flex-direction: row;       /* side-by-side on desktop */
            }
        }
      
     
     
    </style>
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
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

            // Logo preview
            $('#image').change(function(e) {
                let file = e.target.files[0];
                let preview = $('#logo-preview');
                let cameraIcon = $('#logo-camera-icon');
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.attr('src', ev.target.result).removeClass('d-none');
                        cameraIcon.addClass('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.addClass('d-none').attr('src', '');
                    cameraIcon.removeClass('d-none');
                }
            });

            // Primary Images manipulation
            let primarySlotCount = 1;
            const maxPrimary = 4;

            $('#addPrimaryImage').click(function() {
                if (primarySlotCount >= maxPrimary) return;
                primarySlotCount++;
                let idx = primarySlotCount;
                let html = `
                    <div class="primary-image-slot mb-2" data-index="${idx}">
                        <div class="file-upload-box position-relative">
                            <div class="file-upload-icon"><i class="ti ti-upload"></i></div>
                            <div class="file-upload-text text-uppercase small">IMAGE ${idx}</div>
                            <input type="file" name="primary_image[]" accept="image/*" class="primary-img-input">
                            <img class="img-preview d-none" style="max-height:60px; margin-top:6px; border-radius:4px;">
                            <button type="button" class="btn btn-sm btn-danger remove-primary-slot" style="position:absolute; top:4px; right:4px; z-index:5; padding:2px 6px; font-size:0.7rem;">
                                <i class="ti ti-x"></i>
                            </button>
                        </div>
                    </div>`;
                $('#primaryImagesContainer').append(html);
                if (primarySlotCount >= maxPrimary) $(this).hide();
            });

            $(document).on('click', '.remove-primary-slot', function() {
                $(this).closest('.primary-image-slot').remove();
                primarySlotCount--;
                $('#addPrimaryImage').show();
            });

            $(document).on('change', '.primary-img-input', function(e) {
                let file = e.target.files[0];
                let preview = $(this).siblings('.img-preview');
                let text = $(this).siblings('.file-upload-text');
                if (file) {
                    text.text(file.name);
                    let reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.attr('src', ev.target.result).removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    text.text('IMAGE');
                    preview.addClass('d-none').attr('src', '');
                }
            });

            // Form submission
            $("#frmRegister").on("submit", function(event) {
                event.preventDefault();
                let formData = new FormData(this);
                formData.append('password_confirmation', formData.get('password'));

                $.ajax({
                    url: "{{ url('/center_api/auth/register') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('#alertError').addClass('d-none').find('ul').empty();
                        $('#alertSuccess').addClass('d-none').find('ul').empty();
                        $(".btn-register span").text('Processing...');
                        $('.btn-register').prop('disabled', true);
                    },
                    success: function(response, textStatus, xhr) {
                        if (xhr.status === 200 || xhr.status === 201) {
                            $('#alertSuccess').removeClass('d-none').find('ul').html('<li>Registration successful! Your request is under review.</li>');
                            setTimeout(() => {
                                window.location.href = "{{ route('center_user.login') }}";
                            }, 2000);
                        } else {
                            $('#alertError').removeClass('d-none').find('ul').html('<li>Registration failed.</li>');
                        }
                        resetButton();
                    },
                    error: function(response) {
                        let errors = response.responseJSON?.errors;
                        let msg = response.responseJSON?.message;
                        let ul = $('#alertError').removeClass('d-none').find('ul').empty();
                        if (errors) {
                            $.each(errors, function(key, val) {
                                ul.append(`<li>${val[0]}</li>`);
                            });
                        } else if (msg) {
                            ul.append(`<li>${msg}</li>`);
                        } else {
                            ul.append(`<li>Registration failed (status ${response.status})</li>`);
                        }
                        $('html, body').animate({ scrollTop: 0 }, 300);
                        resetButton();
                    }
                });

                function resetButton() {
                    $(".btn-register span").text('Register');
                    $('.btn-register').prop('disabled', false);
                }
            });
        });
    </script>
@endsection

@section('content')
<div class="register-wrapper">
    <!-- Mobile Welcome Banner -->
    <div class="mobile-welcome w-100">
        <div class="logo-sm">
            <img src="{{ asset('logo.svg') }}" alt="Luzori">
        </div>
        <h2 class="h5 fw-bold mt-2">Welcome to Luzori</h2>
        <p class="small mb-0" style="color:#F2E8DC;">Your journey starts here</p>
    </div>

    <!-- Left panel (desktop) -->
    <div class="register-left">
        <div class="register-left-content">
            <h2>Welcome to Luzori</h2>
            <div class="logo-circle">
                <img src="{{ asset('logo.svg') }}" alt="Luzori">
            </div>
            <p>From our shelves to your homes, we plant your satisfaction with every shopping bag.</p>
            <div class="bottom-links">
                <span>Contact Us</span> <span class="mx-2">|</span> <span>Discover More</span>
            </div>
        </div>
    </div>

    <!-- Right panel (form) -->
    <div class="register-right">
        <div class="register-card">
            <h1 class="register-title">Create Your Account</h1>

            <!-- Alerts -->
            <div id="alertError" class="alert alert-danger alert-status d-none" role="alert">
                <ul class="mb-0 ps-3"></ul>
            </div>
            <div id="alertSuccess" class="alert alert-success alert-status d-none" role="alert">
                <ul class="mb-0 ps-3"></ul>
            </div>

            <form id="frmRegister" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Left column -->
                    <div class="col-md-6">
                        <!-- Name Center -->
                        <label class="form-label-bs">Name Center <span class="text-danger">*</span></label>
                        <div class="input-group-icon mb-3">
                            <i class="ti ti-user icon icon-left"></i>
                            <input type="text" name="name" class="form-control-custom ps-icon" placeholder="Name of center" required>
                        </div>

                        <!-- Email -->
                        <label class="form-label-bs">Email Center <span class="text-danger">*</span></label>
                        <div class="input-group-icon mb-3">
                            <i class="ti ti-mail icon icon-left"></i>
                            <input type="email" name="email" class="form-control-custom ps-icon " placeholder="example@email.com" required>
                        </div>

                        <!-- Password -->
                        <label class="form-label-bs">Password <span class="text-danger">*</span></label>
                        <div class="input-group-icon mb-3">
                            <i class="ti ti-lock icon icon-left"></i>
                            <input type="password" name="password" class="form-control-custom ps-icon pe-icon" placeholder="••••••••" required>
                            <span class="icon icon-right toggle-password" style="cursor:pointer;"><i class="ti ti-eye-off"></i></span>
                        </div>

                        <!-- Logo Center -->
                        <label class="form-label-bs">Logo Center</label>
                        <div class="logo-upload-circle mb-3">
                            <i class="ti ti-camera" id="logo-camera-icon" style="font-size:1.5rem; color:var(--brand-dark);"></i>
                            <img id="logo-preview" class="preview d-none" src="" alt="Logo preview">
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                    </div>

                    <!-- Right column -->
                    <div class="col-md-6">
                        <!-- Domain -->
                        <label class="form-label-bs">Domain Center <span class="text-danger">*</span></label>
                        <input type="text" name="domain" class="form-control-custom mb-3" placeholder="center-name" required>

                        <!-- Phone Center -->
                        <label class="form-label-bs">Phone Center <span class="text-danger">*</span></label>
                        <div class="phone-group mb-3">
                            <select name="country_code" required>
                                <option value="971">🇦🇪 +971</option>
                                <option value="966">🇸🇦 +966</option>
                                <option value="974">🇶🇦 +974</option>
                                <option value="965">🇰🇼 +965</option>
                                <option value="968">🇴🇲 +968</option>
                                <option value="973">🇧🇭 +973</option>
                            </select>
                            <input type="text" name="phone" class="form-control-custom" placeholder="503140232" required>
                        </div>

                        <!-- Currency -->
                        <label class="form-label-bs">Currency</label>
                        <select name="currency" class="form-select form-control-custom mb-3 bg-custom">
                            <option value="AED">AED</option>
                            <option value="USD">USD</option>
                            <option value="SAR">SAR</option>
                            <option value="EUR">EUR</option>
                        </select>

                        <!-- Primary Images -->
                        <label class="form-label-bs">Primary Images <small class="text-muted">(1–4)</small></label>
                        <div id="primaryImagesContainer">
                            <div class="primary-image-slot mb-2" data-index="0">
                                <div class="file-upload-box position-relative">
                                    <div class="file-upload-icon"><i class="ti ti-upload"></i></div>
                                    <div class="file-upload-text text-uppercase small">IMAGE 1</div>
                                    <input type="file" name="primary_image[]" accept="image/*" class="primary-img-input">
                                    <img class="img-preview d-none" style="max-height:60px; margin-top:6px; border-radius:4px;">
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addPrimaryImage" class="btn btn-sm btn-outline-secondary mt-1">
                            <i class="ti ti-plus"></i> Add Image
                        </button>
                    </div>
                </div>

                <!-- Terms (optional) -->
                <div class="d-flex align-items-start gap-2 mt-3">
                    <input type="checkbox" class="form-check-input mt-1 " required>
                    <small class="text-muted">
                        I agree to the <strong class="text-dark">Terms of Service</strong> and <strong class="text-dark">Privacy Policy</strong>
                    </small>
                </div>

                <!-- Submit -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-register">
                        <span>Register</span>
                    </button>
                </div>

                <!-- Divider & Google -->
                <!-- <div class="d-flex align-items-center my-4">
                    <hr class="flex-grow-1">
                    <span class="mx-3 text-muted small fw-bold">OR</span>
                    <hr class="flex-grow-1">
                </div> -->

                <!-- <button type="button" class="google-btn w-100">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Sign in with Google
                </button> -->
            </form>
        </div>
    </div>
</div>
@endsection