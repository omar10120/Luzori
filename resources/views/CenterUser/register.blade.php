@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('center_register.page_title'))

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
            /* background: var(--brand-light); */
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
                overflow:scroll;
                
                
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

        /* Logical positions: follow text direction (LTR vs RTL) */
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
            z-index: 2;
        }

        .form-control-custom.is-invalid,
        .phone-group.is-invalid {
            border-color: #dc3545 !important;
        }
        .phone-group.is-invalid select,
        .phone-group.is-invalid input {
            background-color: #fff5f5 !important;
        }
        .invalid-feedback-field {
            font-size: 0.75rem;
            color: #dc3545;
            margin-top: 0.25rem;
            display: none;
        }
        .invalid-feedback-field.is-visible {
            display: block;
        }

        /* Primary images — grid + cards */
        .primary-images-section {
            border: 1px solid var(--border-color);
            border-radius: 0.85rem;
            padding: 0.75rem;
            background: rgba(255, 251, 247, 0.9);
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }
        .primary-images-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.65rem;
            width: 100%;
            min-width: 0;
        }
        @media (min-width: 400px) {
            .primary-images-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .primary-image-slot {
            min-width: 0;
            width: 100%;
        }
        .primary-upload-card {
            position: relative;
            border: 2px dashed var(--border-color);
            border-radius: 0.75rem;
            min-height: 118px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 0.5rem 0.5rem;
            background: var(--brand-light);
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }
        /* File input must overlay the card (not in document flow) — was only styled under .file-upload-box */
        .primary-upload-card input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            min-height: 118px;
            margin: 0;
            padding: 0;
            border: 0;
            opacity: 0;
            cursor: pointer;
            z-index: 4;
            font-size: 0;
        }
        @media (min-width: 768px) {
            .primary-upload-card {
                background: rgba(255, 214, 168, 0.25);
            }
        }
        .primary-upload-card:hover {
            border-color: var(--brand-dark);
            background: rgba(255, 214, 168, 0.45);
            box-shadow: 0 4px 12px rgba(34, 93, 92, 0.08);
        }
        .primary-upload-card.has-preview {
            border-style: solid;
            border-color: var(--brand-dark);
            padding: 0.35rem;
        }
        .primary-upload-card.has-preview .primary-upload-meta {
            display: none;
        }
        .primary-upload-card .img-preview {
            position: relative;
            z-index: 1;
            pointer-events: none;
            width: 100%;
            max-height: 100px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-top: 0;
        }
        .primary-upload-card .file-upload-icon {
            font-size: 1.35rem;
            color: var(--brand-dark);
            opacity: 0.85;
            margin-bottom: 0.2rem;
        }
        .primary-upload-meta {
            text-align: center;
            pointer-events: none;
            z-index: 0;
        }
        .primary-slot-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: var(--brand-dark);
        }
        .primary-slot-hint {
            font-size: 8px;
            color: var(--text-muted);
            margin-top: 0.15rem;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding: 0 4px;
        }
        .remove-primary-slot {
            position: absolute;
            top: 6px;
            right: 6px;
            z-index: 6;
            width: 26px;
            height: 26px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.75rem;
            line-height: 1;
        }
        #addPrimaryImage {
            border-color: var(--brand-dark);
            color: var(--brand-dark);
            font-weight: 600;
            border-radius: 50rem;
        }
        #addPrimaryImage:hover {
            background: var(--brand-dark);
            color: var(--brand-peach);
        }

        .logo-upload-circle.is-invalid {
            border-color: #dc3545;
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
            border-radius: 0 0 34px 34px !important;
            padding: 1rem 1rem 1.5rem !important;
            text-align: center;
            display: block  !important;
            
        }
        .mobile-welcome h2{
            color: var(--brand-peach) !important;
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
            background-color: var(--brand-dark) !important;
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

                /* Modern browsers */
        .form-control-custom::placeholder {
            color: var(--brand-dark); /* Or any color */
            opacity: 0.5; /* Adjust between 0 and 1 */
        }

        /* Specific support for Internet Explorer / Edge */
        .form-control-custom:-ms-input-placeholder {
            opacity: 0.5;
        }

        /* Specific support for Firefox */
        .form-control-custom::-moz-placeholder {
            opacity: 0.5;
        }

      
     
     
    </style>
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            const MAX_IMAGE_BYTES = 4096 * 1024; // matches RegisterRequest max:4096 (KB)
            const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            const DOMAIN_RE = /^(?!-)[a-z0-9-]+(?<!-)$/;

            // Prevent Arabic characters in the email input field
            const emailInput = document.getElementById('emailInput');
            if (emailInput) {
                emailInput.addEventListener('beforeinput', (event) => {
                    // Check if the data being inserted contains any Arabic Unicode characters
                    if (event.data && /[\u0600-\u06FF]/.test(event.data)) {
                        event.preventDefault(); // Stop the input
                        return false;
                    }
                });

                // Also prevent pasting Arabic text
                emailInput.addEventListener('paste', (event) => {
                    let pastedText = (event.clipboardData || window.clipboardData).getData('text');
                    if (/[\u0600-\u06FF]/.test(pastedText)) {
                        event.preventDefault(); // Stop the paste
                        // Optional: Show a small alert or the existing error message
                        $(emailInput).addClass('is-invalid');
                        $('[data-field-error="email"]').addClass('is-visible').text('Email cannot contain Arabic characters.');
                        return false;
                    }
                });
            }


            function showFieldError(name, message) {
                const $el = $('[data-field-error="' + name + '"]');
                $el.text(message).addClass('is-visible');
                const $input = $('[name="' + name + '"]');
                if (name === 'phone' || name === 'country_code') {
                    $input.closest('.phone-group').addClass('is-invalid');
                } else if (name === 'image') {
                    $input.addClass('is-invalid');
                    $input.closest('.logo-upload-circle').addClass('is-invalid');
                } else {
                    $input.addClass('is-invalid');
                }
            }
            function clearFieldErrors() {
                $('.invalid-feedback-field').removeClass('is-visible').text('');
                $('.form-control-custom, .form-check-input').removeClass('is-invalid');
                $('.phone-group').removeClass('is-invalid');
                $('#image').closest('.logo-upload-circle').removeClass('is-invalid');
            }
            function validateImageFile(file) {
                if (!file) return { ok: true };
                if (file.size > MAX_IMAGE_BYTES) {
                    return { ok: false, message: 'Image must be 4MB or smaller.' };
                }
                if (file.type && !ALLOWED_IMAGE_MIMES.includes(file.type)) {
                    return { ok: false, message: 'Use JPG, PNG, or GIF only.' };
                }
                const ext = file.name.split('.').pop().toLowerCase();
                if (!['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    return { ok: false, message: 'Use JPG, PNG, or GIF only.' };
                }
                return { ok: true };
            }

            function validateForm() {
                clearFieldErrors();
                let ok = true;
                const name = ($('input[name="name"]').val() || '').trim();
                if (name.length < 2) {
                    showFieldError('name', 'Enter a center name (at least 2 characters).');
                    ok = false;
                } else if (name.length > 255) {
                    showFieldError('name', 'Name is too long (max 255 characters).');
                    ok = false;
                }

                const email = ($('input[name="email"]').val() || '').trim();
                const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email || !emailRe.test(email)) {
                    showFieldError('email', 'Enter a valid email address.');
                    ok = false;
                }

                const password = $('input[name="password"]').val() || '';
                if (password.length < 6) {
                    showFieldError('password', 'Password must be at least 6 characters.');
                    ok = false;
                } else if (password.length > 15) {
                    showFieldError('password', 'Password must be at most 15 characters.');
                    ok = false;
                }

                const domain = ($('input[name="domain"]').val() || '').trim().toLowerCase();
                $('input[name="domain"]').val(domain);
                if (domain.length < 3 || domain.length > 50) {
                    showFieldError('domain', 'Domain must be 3-20 characters.');
                    ok = false;
                } else if (!DOMAIN_RE.test(domain)) {
                    showFieldError('domain', 'Use lowercase letters, numbers, and hyphens only. No spaces or leading/trailing hyphens.');
                    ok = false;
                }

                const phoneDigits = ($('input[name="phone"]').val() || '').replace(/\D/g, '');
                if (phoneDigits.length < 6 || phoneDigits.length > 15) {
                    showFieldError('phone', 'Phone must be 8-9 digits.');
                    ok = false;
                }

                const logo = document.getElementById('image')?.files?.[0];
                const logoCheck = validateImageFile(logo);
                if (!logoCheck.ok) {
                    showFieldError('image', logoCheck.message);
                    $('#image').closest('.logo-upload-circle').addClass('is-invalid');
                    ok = false;
                }

                $('.primary-img-input').each(function() {
                    const f = this.files && this.files[0];
                    if (f) {
                        const chk = validateImageFile(f);
                        if (!chk.ok) {
                            const $slot = $(this).closest('.primary-image-slot');
                            $slot.find('.primary-slot-hint').text(chk.message).css('color', '#dc3545');
                            $slot.find('.primary-upload-card').addClass('border-danger');
                            ok = false;
                        }
                    }
                });

                if (!$('input[name="terms_accept"]').is(':checked')) {
                    showFieldError('terms_accept', 'You must accept the terms to continue.');
                    ok = false;
                }

                return ok;
            }

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
                const $circle = $(this).closest('.logo-upload-circle');
                $circle.removeClass('is-invalid');
                $('[data-field-error="image"]').removeClass('is-visible').text('');
                if (file) {
                    const chk = validateImageFile(file);
                    if (!chk.ok) {
                        showFieldError('image', chk.message);
                        $circle.addClass('is-invalid');
                        e.target.value = '';
                        preview.addClass('d-none').attr('src', '');
                        cameraIcon.removeClass('d-none');
                        return;
                    }
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

            function renumberPrimarySlots() {
                $('#primaryImagesContainer .primary-image-slot').each(function(i) {
                    $(this).find('.primary-slot-label').text('Image ' + (i + 1));
                });
                primarySlotCount = $('#primaryImagesContainer .primary-image-slot').length;
                if (primarySlotCount >= maxPrimary) {
                    $('#addPrimaryImage').hide();
                } else {
                    $('#addPrimaryImage').show();
                }
            }

            function primarySlotHtml(idx, withRemove) {
                const removeBtn = withRemove
                    ? '<button type="button" class="btn btn-sm btn-danger remove-primary-slot" aria-label="Remove image"><i class="ti ti-x"></i></button>'
                    : '';
                return `
                    <div class="primary-image-slot" data-index="${idx}">
                        <div class="primary-upload-card position-relative">
                            ${removeBtn}
                            <div class="primary-upload-meta">
                                <div class="file-upload-icon"><i class="ti ti-upload"></i></div>
                                <div class="primary-slot-label">Image ${idx}</div>
                                <div class="primary-slot-hint ">JPG, PNG, GIF · max 4MB</div>
                            </div>
                            <input type="file" name="primary_image[]" accept="image/jpeg,image/png,image/gif,.jpg,.jpeg,.png,.gif" class="primary-img-input">
                            <img class="img-preview d-none" alt="">
                        </div>
                    </div>`;
            }

            renumberPrimarySlots();

            $('#addPrimaryImage').click(function() {
                if (primarySlotCount >= maxPrimary) return;
                primarySlotCount++;
                $('#primaryImagesContainer').append(primarySlotHtml(primarySlotCount, true));
                renumberPrimarySlots();
            });

            $(document).on('click', '.remove-primary-slot', function() {
                $(this).closest('.primary-image-slot').remove();
                renumberPrimarySlots();
            });

            $(document).on('change', '.primary-img-input', function(e) {
                let file = e.target.files[0];
                let $card = $(this).closest('.primary-upload-card');
                let preview = $card.find('.img-preview');
                let hint = $card.find('.primary-slot-hint');
                $card.removeClass('border-danger');
                if (file) {
                    const chk = validateImageFile(file);
                    if (!chk.ok) {
                        hint.text(chk.message).css('color', '#dc3545');
                        $card.addClass('border-danger');
                        e.target.value = '';
                        preview.addClass('d-none').attr('src', '');
                        $card.removeClass('has-preview');
                        return;
                    }
                    hint.text(file.name).css('color', 'var(--text-muted)');
                    let reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.attr('src', ev.target.result).removeClass('d-none');
                        $card.addClass('has-preview');
                    };
                    reader.readAsDataURL(file);
                } else {
                    hint.text('JPG, PNG, GIF · max 4MB').css('color', 'var(--text-muted)');
                    preview.addClass('d-none').attr('src', '');
                    $card.removeClass('has-preview');
                }
            });

            $('input[name="domain"]').on('blur', function() {
                const v = ($(this).val() || '').trim().toLowerCase();
                $(this).val(v);
            });

            $('input[name="phone"]').on('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });

            $('#frmRegister').on('input change', 'input[name="name"], input[name="email"], input[name="password"], input[name="domain"], input[name="phone"], input[name="terms_accept"]', function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                const arabicRegex = /[\u0600-\u06FF]/;

                // 1. Always reset states for the field being changed
                $(this).removeClass('is-invalid');
                $('[data-field-error="' + name + '"]').removeClass('is-visible').text('');
                
                if (name === 'phone') {
                    $(this).closest('.phone-group').removeClass('is-invalid');
                }

                // 2. Specific Validation: Only check Arabic for the email field
                if (name === 'email' && arabicRegex.test(value)) {
                    $(this).addClass('is-invalid');
                    $('[data-field-error="email"]')
                        .addClass('is-visible')
                        .text('Email cannot contain Arabic characters.');
                }
            });


            // Form submission
            $("#frmRegister").on("submit", function(event) {
                event.preventDefault();
                if (!validateForm()) {
                    $('html, body').animate({ scrollTop: $('.register-card').offset().top - 24 }, 300);
                    return;
                }


                //add AE7 for iban if not exsist 
                let formData = new FormData(this);
                formData.set('phone', ($('input[name="phone"]').val() || '').replace(/\D/g, ''));
                formData.append('password_confirmation', formData.get('password'));

                // Handle IBAN - add AE7 prefix if not present
                let ibanValue = formData.get('bank_name');
                if (ibanValue && !ibanValue.startsWith('AE')) {
                    ibanValue = 'AE' + ibanValue;
                    formData.set('bank_name', ibanValue);
                }


                $.ajax({
                    url: "{{ url('/center_api/auth/register') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        clearFieldErrors();
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
                        clearFieldErrors();
                        let ul = $('#alertError').find('ul').empty();
                        const inlineFields = ['name', 'email', 'domain', 'phone', 'password', 'country_code', 'image'];
                        if (errors) {
                            $.each(errors, function(key, val) {
                                const fieldKey = key.split('.')[0];
                                const text = val[0];
                                if (inlineFields.indexOf(fieldKey) !== -1 && $('[data-field-error="' + fieldKey + '"]').length) {
                                    showFieldError(fieldKey, text);
                                } else {
                                    ul.append('<li>' + text + '</li>');
                                }
                            });
                        } else if (msg) {
                            ul.append('<li>' + msg + '</li>');
                        } else {
                            ul.append('<li>Registration failed (status ' + response.status + ')</li>');
                        }
                        if (ul.children().length) {
                            $('#alertError').removeClass('d-none');
                        } else if ($('.invalid-feedback-field.is-visible').length) {
                            $('#alertError').addClass('d-none');
                        } else {
                            ul.append('<li>Could not complete registration.</li>');
                            $('#alertError').removeClass('d-none');
                        }
                        $('html, body').animate({ scrollTop: $('.register-card').offset().top - 24 }, 300);
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
<div class="register-wrapper" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="register-lang-fixed">
        @include('components.lang_switcher', ['variant' => 'register'])
    </div>

    <!-- Mobile Welcome Banner -->
    <div class="mobile-welcome w-100">
        <div class="logo-sm">
            <img src="{{ asset('logo_test.svg') }}" alt="{{ __('center_register.meta_title') }}">
        </div>
        <h2 class="h5 fw-bold mt-2">{{ __('center_register.mobile_welcome_h2') }}</h2>
        <p class="small mb-0" style="color:#F2E8DC;">{{ __('center_register.mobile_welcome_sub') }}</p>
    </div>

    <!-- Left panel (desktop) -->
    <div class="register-left">
        <div class="register-left-content">
            <h2>{{ __('center_register.left_panel_title') }}</h2>
            <div class="logo-circle">
                <img src="{{ asset('logo_test.svg') }}" alt="{{ __('center_register.meta_title') }}">
            </div>
            <p>{{ __('center_register.left_panel_text') }}</p>
            <div class="bottom-links fw-bold">
                <span>{{ __('center_register.contact_us') }}</span> <span class="mx-2">|</span> <span>{{ __('center_register.discover_more') }}</span>
            </div>
        </div>
    </div>

    <!-- Right panel (form) -->
    <div class="register-right">
        <div class="register-card">
            <h1 class="register-title">{{ __('center_register.create_account') }}</h1>

            <!-- Alerts -->
            <div id="alertError" class="alert alert-danger alert-status d-none" role="alert">
                <ul class="mb-0 ps-3"></ul>
            </div>
            <div id="alertSuccess" class="alert alert-success alert-status d-none" role="alert">
                <ul class="mb-0 ps-3"></ul>
            </div>

            <form id="frmRegister" enctype="multipart/form-data" novalidate autocomplete="on">
                <div class="row g-3">
                    <!-- Left column -->
                    <div class="col-md-6">
                        <!-- Name Center -->
                        <label class="form-label-bs">{{ __('center_register.label_name') }} <span class="text-danger">*</span></label>
                        <div class="input-group-icon mb-1">
                            <i class="ti ti-user icon icon-inline-start"></i>
                            <input type="text" name="name" class="form-control-custom pad-inline-start" placeholder="{{ __('center_register.placeholder_name') }}" required minlength="2" maxlength="255" autocomplete="organization">
                        </div>
                        <div class="invalid-feedback-field mb-2" data-field-error="name"></div>

                           <!-- Domain -->
                           <label class="form-label-bs">{{ __('center_register.label_domain') }} <span class="text-danger">*</span> <small class="text-muted fw-normal">{{ __('center_register.domain_hint') }}</small></label>
                        <input type="text" name="domain" class="form-control-custom mb-1" placeholder="{{ __('center_register.placeholder_domain') }}" required minlength="3" maxlength="20" pattern="[a-z0-9]([a-z0-9-]*[a-z0-9])?" title="Lowercase letters, numbers, hyphens. 3–50 characters." autocapitalize="none" spellcheck="false" autocomplete="off">
                        <div class="invalid-feedback-field mb-2" data-field-error="domain"></div>

                        <!-- Email -->
                        <label class="form-label-bs">{{ __('center_register.label_email') }} <span class="text-danger">*</span></label>
                        <div class="input-group-icon mb-1">
                            <i class="ti ti-mail icon icon-inline-start"></i>
                            <input type="email" name="email" id="emailInput" class="form-control-custom pad-inline-start" placeholder="{{ __('center_register.placeholder_email') }}" required maxlength="255" autocomplete="email">
                        </div>
                        <div class="invalid-feedback-field mb-2" data-field-error="email"></div>
                        <!-- Password -->
                        <label class="form-label-bs">{{ __('center_register.label_password') }} <span class="text-danger">*</span> <small class="text-muted fw-normal">{{ __('center_register.password_hint') }}</small></label>
                        <div class="input-group-icon mb-1">
                            <i class="ti ti-lock icon icon-inline-start"></i>
                            <input type="password" name="password" class="form-control-custom pad-inline-start pad-inline-end" placeholder="{{ __('center_register.placeholder_password') }}" required minlength="6" maxlength="15" autocomplete="new-password">
                            <span class="icon icon-inline-end toggle-password" style="cursor:pointer;"><i class="ti ti-eye-off"></i></span>
                        </div>
                        <div class="invalid-feedback-field mb-2" data-field-error="password"></div>

         
                    </div>

                    <!-- Right column -->
                    <div class="col-md-6">
                     

                        <!-- Phone Center -->
                        <label class="form-label-bs">{{ __('center_register.label_phone') }} <span class="text-danger">*</span></label>
                        <div class="phone-group mb-1">
                            <select name="country_code" required>
                                <option value="971">🇦🇪 +971</option>
                                <option value="966">🇸🇦 +966</option>
                                <option value="974">🇶🇦 +974</option>
                                <option value="965">🇰🇼 +965</option>
                                <option value="968">🇴🇲 +968</option>
                                <option value="973">🇧🇭 +973</option>
                            </select>
                            <input type="tel" name="phone" class="form-control-custom" inputmode="numeric" placeholder="{{ __('center_register.placeholder_phone') }}" required minlength="8" maxlength="9" pattern="[0-9]{6,15}" autocomplete="tel">
                        </div>
                        <div class="invalid-feedback-field mb-2" data-field-error="phone"></div>

                        <!-- IBAN Number -->
                        <label class="form-label-bs">{{ __('center_register.label_iban') }}
                        <small class="text-muted fw-normal">{{ __('center_register.iban_optional') }}</small>
                        </label>
                        <input type="text" name="bank_name" class="form-control-custom mb-3" placeholder="{{ __('center_register.placeholder_iban') }}" maxlength="21" autocomplete="off">

                        <!-- Currency -->
                        <label class="form-label-bs">{{ __('center_register.label_currency') }}</label>
                        <select name="currency" class="form-select form-control-custom mb-3 bg-custom">
                            <option value="AED">AED</option>
                            <option value="USD">USD</option>
                            <option value="SAR">SAR</option>
                            <option value="EUR">EUR</option>
                        </select>

                        <!-- Logo Center -->
                        <label class="form-label-bs">{{ __('center_register.label_logo') }} <small class="text-muted fw-normal">{{ __('center_register.logo_hint') }}</small></label>
                        <div class="logo-upload-circle mb-1">
                            <img id="logo-camera-icon" src="{{ asset('camera.svg') }}" alt="" width="28" height="28" class="logo-camera-placeholder" style="object-fit:contain; position:relative; z-index:1;">
                            <img id="logo-preview" class="preview d-none" src="" alt="{{ __('center_register.label_logo') }}">
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,.jpg,.jpeg,.png,.gif">
                        </div>
                        <div class="invalid-feedback-field mb-2" data-field-error="image"></div>

                        <!-- Primary Images -->
                        <label class="form-label-bs d-block">{{ __('center_register.label_primary_images') }}
                            <small class="text-muted fw-normal">{{ __('center_register.primary_images_hint') }}</small>
                        </label>
                        <div class="primary-images-section mb-2">
                            <div id="primaryImagesContainer" class="primary-images-grid">
                                <div class="primary-image-slot" data-index="1">
                                    <div class="primary-upload-card position-relative">
                                        <div class="primary-upload-meta">
                                            <div class="file-upload-icon"><i class="ti ti-upload"></i></div>
                                            <div class="primary-slot-label">{{ __('center_register.primary_image_label', ['n' => 1]) }}</div>
                                            <div class="primary-slot-hint">{{ __('center_register.primary_slot_hint') }}</div>
                                        </div>
                                        <input type="file" name="primary_image[]" accept="image/jpeg,image/png,image/gif,.jpg,.jpeg,.png,.gif" class="primary-img-input">
                                        <img class="img-preview d-none" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="addPrimaryImage" class="btn btn-sm btn-outline-secondary mt-1">
                            <i class="ti ti-plus"></i> 
                            {{ __('center_register.add_image') }}
                        </button>
                    </div>
                </div>

                <!-- Terms (optional) -->
                <div class="d-flex align-items-start gap-2 mt-3">
                    <input type="checkbox" name="terms_accept" class="form-check-input mt-1" required>
                    <small class="text-muted">
                    {{ __('center_register.i_agree_to_the') }}
                         <strong class="text-dark">
                            {{ __('center_register.terms_of_service') }}
                        </strong> 
                        {{ __('center_register.and') }}
                         <strong class="text-dark">
                            {{ __('center_register.privacy_policy') }}
                        </strong>
                    </small>
                </div>
                <div class="invalid-feedback-field ms-4" data-field-error="terms_accept"></div>

                <!-- Submit -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-register">
                        <span>{{ __('center_register.register') }}</span>
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