@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.settings-form-styles')
    <style>
        .lz-settings-page .lz-contact-phone-row > [class*="col"] {
            display: flex;
            flex-direction: column;
        }
        .lz-settings-page .lz-contact-phone-row #country_code_container > .mb-1 {
            margin-bottom: 0 !important;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .lz-settings-page .lz-contact-phone-row .lz-field-label,
        .lz-settings-page .lz-contact-phone-row .form-label {
            font-weight: 700;
            color: #002b26;
            font-size: 0.92rem;
            margin-bottom: 0.45rem;
            line-height: 1.35;
            min-height: 1.35rem;
        }
        .lz-settings-page .lz-contact-phone-row .lz-field-help {
            display: none;
        }
        .lz-settings-page .lz-contact-phone-row .input-group {
            margin-top: auto;
            flex-wrap: nowrap;
        }
        .lz-settings-page .lz-contact-phone-row select[name="country_code"] {
            min-width: 0;
            text-overflow: ellipsis;
        }
        .lz-settings-page #country_code_container .form-control,
        .lz-settings-page #country_code_container select {
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-settings-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-address-book"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h2 class="mb-0">{{ __('locale.infos') ?? $title }}</h2>
                        <small class="text-muted">{{ __('general.manage_your_contact_info_settings') ?? 'Manage your invoice contact details.' }}</small>
                    </div>
                    <button type="submit" class="btn btn-luzori-primary submitFrom d-flex align-items-center gap-1">
                        <i class="ti ti-check"></i>
                        <span>{{ __('general.save') }}</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="lz-section-label">
                        <img src="{{ asset('assets/icons/contact-info.svg') }}" alt="">
                        <span>{{ __('general.contact_info') }}</span>
                    </div>

                    <div class="row g-3 lz-contact-phone-row">
                        <div class="col-12 col-md-4" id="country_code_container">
                            @include('Admin.Components.country_code', ['item' => $item])
                        </div>
                        <div class="col-6 col-md-2" id="phone_prefix_container" style="display: {{ ($item && $item->country_code == '+971') ? 'block' : 'none' }};">
                            <label class="lz-field-label">Prefix</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="ti ti-phone"></i>
                                </span>
                                <select class="form-control" name="phone_prefix" id="phone_prefix">
                                    @php
                                        $prefixes = ['50', '52', '54', '55', '56', '58'];
                                        $currentPrefix = '';
                                        $phoneWithoutPrefix = $item ? (string)$item->phone : '';
                                        if ($item && $item->country_code == '+971' && $item->phone) {
                                            $phoneStr = (string)$item->phone;
                                            foreach ($prefixes as $prefix) {
                                                if (str_starts_with($phoneStr, $prefix)) {
                                                    $currentPrefix = $prefix;
                                                    $phoneWithoutPrefix = substr($phoneStr, strlen($prefix));
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    @foreach ($prefixes as $prefix)
                                        <option value="{{ $prefix }}" {{ $currentPrefix == $prefix ? 'selected' : '' }}>
                                            {{ $prefix }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-{{ ($item && $item->country_code == '+971') ? '6' : '8' }}" id="phone_input_container">
                            <label class="lz-field-label">{{ __('field.phone') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="ti ti-phone"></i>
                                </span>
                                <input type="tel" class="form-control" name="phone" id="phone" value="{{ $phoneWithoutPrefix }}"
                                    placeholder="{{ __('field.phone') }}"
                                    required
                                    maxlength="8"
                                    pattern="5[0-9]{7}"
                                    title="Phone number must start with 5 and be exactly 8 digits" />
                            </div>
                        </div>
                        <div class="col-12 col-md-6" id="email_input_container">
                            <label class="lz-field-label">{{ __('field.email') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_email_address') }}</small>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="ti ti-mail"></i>
                                </span>
                                <input type="text" class="form-control" name="email" value="{{ $item->email }}"
                                    placeholder="{{ __('field.email') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="lz-section-label mt-3">
                        <img src="{{ asset('assets/icons/social-media.svg') }}" alt="">
                        <span>{{ __('general.social_media_links') }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.facebook') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_facebook_url') }}</small>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img src="{{ asset('assets/icons/facebook.svg') }}" alt="" style="width:20px;height:20px;">
                                </span>
                                <input type="text" class="form-control" name="facebook" value="{{ $item->facebook }}"
                                    placeholder="{{ __('field.facebook') }}" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.instagram') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_instagram_url') }}</small>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img src="{{ asset('assets/icons/instagram.svg') }}" alt="" style="width:20px;height:20px;">
                                </span>
                                <input type="text" class="form-control" name="instagram" value="{{ $item->instagram }}"
                                    placeholder="{{ __('field.instagram') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.twitter') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_twitter_url') }}</small>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img src="{{ asset('assets/icons/twitter.svg') }}" alt="" style="width:20px;height:20px;">
                                </span>
                                <input type="text" class="form-control" name="twitter" value="{{ $item->twitter }}"
                                    placeholder="{{ __('field.twitter') }}" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.whatsapp') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_whatsapp_url') }}</small>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img src="{{ asset('assets/icons/whatsapp.svg') }}" alt="" style="width:20px;height:20px;">
                                </span>
                                <input type="text" class="form-control" name="whatsapp" value="{{ $item->whatsapp }}"
                                    placeholder="{{ __('field.whatsapp') }}" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.youtube') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_youtube_url') ?? 'Enter the YouTube URL' }}</small>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img src="{{ asset('assets/icons/youtube.svg') }}" alt="" style="width:20px;height:20px;">
                                </span>
                                <input type="text" class="form-control" name="youtube" value="{{ $item->youtube }}"
                                    placeholder="{{ __('field.youtube') }}" />
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.linkedin') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.linkedin') }}</small>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <img src="{{ asset('assets/icons/linkedin.svg') }}" alt="" style="width:20px;height:20px;">
                                </span>
                                <input type="text" class="form-control" name="linkedin" value="{{ $item->linkedin }}"
                                    placeholder="{{ __('field.linkedin') }}" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('vendor-script')
@endsection

@section('page-script')
    <script>
        // Phone prefix toggle for UAE (+971)
        document.addEventListener('DOMContentLoaded', function() {
            const countryCodeSelect = document.querySelector('select[name="country_code"]');
            const phonePrefixContainer = document.getElementById('phone_prefix_container');
            const phoneInputContainer = document.getElementById('phone_input_container');
            const phoneInput = document.getElementById('phone');
            const phonePrefixSelect = document.getElementById('phone_prefix');

            function togglePhonePrefix() {
                if (countryCodeSelect && countryCodeSelect.value === '+971') {
                    phonePrefixContainer.style.display = 'block';
                    phoneInputContainer.classList.remove('col-md-8');
                    phoneInputContainer.classList.add('col-md-6');
                } else {
                    phonePrefixContainer.style.display = 'none';
                    phoneInputContainer.classList.remove('col-md-6');
                    phoneInputContainer.classList.add('col-md-8');
                }
            }

            // Initial check on page load
            togglePhonePrefix();

            // Listen for country code changes
            if (countryCodeSelect) {
                countryCodeSelect.addEventListener('change', togglePhonePrefix);
            }

            // Combine prefix with phone number on form submit
            const form = document.getElementById('frmSubmit');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (countryCodeSelect && countryCodeSelect.value === '+971' && phonePrefixSelect && phoneInput) {
                        const prefix = phonePrefixSelect.value;
                        const phone = phoneInput.value;
                        if (prefix && phone) {
                            // Combine prefix with phone number
                            phoneInput.value = prefix + phone;
                        }
                    }
                });
            }
        });
    </script>

    @include('Admin.Components.submit-form-ajax')
@endsection
