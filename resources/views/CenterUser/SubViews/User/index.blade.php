@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-customer-form-page .form-label {
            display: block;
            font-weight: 700;
            color: #002b26;
            margin-bottom: 0.2rem;
            font-size: 0.92rem;
        }
        .lz-customer-form-page .lz-default-check {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            background: #fbfaf7;
            border: 1px solid #efe6d8;
            border-radius: 12px;
            padding: 0.75rem 1rem;
        }
        .lz-customer-form-page .lz-default-check .form-check-input {
            margin: 0;
            border-color: #c5a070;
            width: 1.15em;
            height: 1.15em;
        }
        .lz-customer-form-page .lz-default-check .form-check-input:checked {
            background-color: #002b26;
            border-color: #002b26;
        }
        .lz-customer-form-page .lz-default-check .lz-field-label {
            margin: 0;
        }
        .lz-customer-form-page .image-upload-dropzone {
            width: 100% !important;
            min-height: 160px;
            border: 2px dashed #b7c9c4 !important;
            border-radius: 16px !important;
            background: #eef5f3 !important;
            padding: 2.5rem 1.5rem !important;
        }
        .lz-customer-form-page .image-upload-dropzone:hover {
            background: #e5f0ed !important;
        }
        .lz-customer-form-page .image-upload-dropzone.dragover {
            background: #dceae6 !important;
            border-color: #002b26 !important;
        }
        .lz-customer-form-page .dropzone-icon i {
            color: #0a3a34 !important;
            font-size: 2.75rem !important;
        }
        .lz-customer-form-page .dropzone-text {
            color: #0a3a34 !important;
            font-weight: 600 !important;
        }
        .lz-customer-form-page .image-preview-wrapper {
            border-radius: 14px;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-customer-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-user-plus"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.users') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.users') }}
                    @endif
                </h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.first_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name"
                                placeholder="{{ __('field.first_name') }}" value="{{ $item ? $item->first_name : null }}" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.last_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="last_name"
                                placeholder="{{ __('field.last_name') }}" value="{{ $item ? $item->last_name : null }}" required />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.email') }}</label>
                            <input type="email" name="email" class="form-control dt-email"
                                placeholder="example@domain.com" value="{{ $item ? $item->email : null }}" />
                        </div>
                        <div class="col-md-2 mb-3">
                            @include('Admin.Components.country_code', ['item' => $item])
                        </div>
                        <div class="col-md-2 mb-3" id="phone_prefix_container" style="display: {{ ($item && $item->country_code == '+971') ? 'block' : 'none' }};">
                            <label class="lz-field-label">Prefix</label>
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
                        <div class="col-md-{{ ($item && $item->country_code == '+971') ? '2' : '4' }} mb-3" id="phone_input_container">
                            <label class="lz-field-label">{{ __('field.mobile_number') }} <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" id="phone" maxlength="7" class="form-control"
                                placeholder="{{ __('field.mobile_number') }}" value="{{ $phoneWithoutPrefix }}"
                                required
                                pattern="[0-9]{7}"
                                title="{{ __('field.phone_must_be_7_digits') }}" />
                            <div class="invalid-feedback" id="phone-invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="lz-default-check">
                                <input type="hidden" name="is_default" value="0" />
                                <input type="checkbox" name="is_default" value="1" id="is_default"
                                    class="form-check-input"
                                    {{ $item && $item->is_default ? 'checked' : '' }} />
                                <label class="lz-field-label" for="is_default">{{ __('field.is_default') }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            @include('CenterUser.Components.image', [
                                'item' => $item,
                                'name' => 'image',
                                'model' => 'user',
                            ])
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.users.index') }}" class="btn btn-luzori-beige">
                        {{ __('general.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-luzori-primary submitFrom">
                        <i class="ti ti-check me-1"></i>
                        <span>{{ __('general.save') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('vendor-script')
@endsection

@section('page-script')
    @include('CenterUser.Components.image-js')

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
                    phoneInputContainer.classList.remove('col-md-4');
                    phoneInputContainer.classList.add('col-md-2');
                } else {
                    phonePrefixContainer.style.display = 'none';
                    phoneInputContainer.classList.remove('col-md-2');
                    phoneInputContainer.classList.add('col-md-4');
                }
            }

            // Initial check on page load
            togglePhonePrefix();

            // Listen for country code changes
            if (countryCodeSelect) {
                countryCodeSelect.addEventListener('change', togglePhonePrefix);
            }

            function validatePhone() {
                if (!phoneInput) return true;
                const value = (phoneInput.value || '').trim();
                const isValid = value.length === 7 && /^[0-9]+$/.test(value);
                const feedbackEl = document.getElementById('phone-invalid-feedback');
                if (!isValid) {
                    phoneInput.classList.add('is-invalid');
                    if (feedbackEl) feedbackEl.textContent = value.length > 0 ? '{{ __('field.phone_must_be_7_digits') }}' : '';
                } else {
                    phoneInput.classList.remove('is-invalid');
                    if (feedbackEl) feedbackEl.textContent = '';
                }
                return isValid;
            }

            if (phoneInput) {
                phoneInput.addEventListener('input', validatePhone);
                phoneInput.addEventListener('blur', validatePhone);
            }

            const form = document.getElementById('frmSubmit');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Validate phone first (before modifying value)
                    if (!validatePhone()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    // If validation passes and UAE country code is selected, combine prefix + phone
                    // Use a hidden field to submit the full number, keeping visible input unchanged
                    if (countryCodeSelect && countryCodeSelect.value === '+971' && phonePrefixSelect && phoneInput) {
                        const prefix = phonePrefixSelect.value;
                        const phone = phoneInput.value.trim();
                        if (prefix && phone) {
                            // Remove any existing hidden phone field
                            const existingHidden = form.querySelector('input[name="phone"][type="hidden"]');
                            if (existingHidden) {
                                existingHidden.remove();
                            }

                            // Temporarily rename the visible input so it's not submitted
                            const originalName = phoneInput.name;
                            phoneInput.name = 'phone_display_only';

                            // Create hidden input with the full number (prefix + phone)
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'phone';
                            hiddenInput.value = prefix + phone;
                            form.appendChild(hiddenInput);

                            // Restore the original name after form submission (in case of validation error)
                            setTimeout(function() {
                                phoneInput.name = originalName;
                                const hiddenField = form.querySelector('input[name="phone"][type="hidden"]');
                                if (hiddenField) {
                                    hiddenField.remove();
                                }
                            }, 1000);
                        }
                    }
                });
            }
        });
    </script>

    @include('CenterUser.Components.submit-form-ajax')
@endsection
