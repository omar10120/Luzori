@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
    <style>
        .lz-worker-form-page .lz-field-label {
            display: block;
            font-weight: 700;
            color: #002b26;
            margin-bottom: 0.2rem;
            font-size: 0.92rem;
        }
        .lz-worker-form-page .lz-field-help {
            display: block;
            color: #7a8582;
            font-size: 0.78rem;
            margin-bottom: 0.4rem;
        }
        .lz-worker-form-page .form-control,
        .lz-worker-form-page .form-select {
            border-radius: 12px !important;
            border-color: #e5ddd2 !important;
        }
        .lz-worker-form-page .form-control:focus,
        .lz-worker-form-page .form-select:focus {
            border-color: #c5a070 !important;
            box-shadow: 0 0 0 3px rgba(197, 160, 112, 0.18) !important;
        }
        .lz-worker-form-page .select2-container--default .select2-selection--single,
        .lz-worker-form-page .select2-container--default .select2-selection--multiple {
            border-radius: 12px !important;
            border-color: #e5ddd2 !important;
            min-height: 44px;
        }
        .lz-worker-form-page .select2-container--default .select2-selection--single {
            padding-top: 0.35rem;
        }
        .lz-worker-form-page .lz-switch-wrap {
            padding: 0.85rem 1rem;
            background: #fbfaf7;
            border: 1px solid #efe6d8;
            border-radius: 12px;
        }
        .lz-worker-form-page .form-check-input:checked {
            background-color: #002b26 !important;
            border-color: #002b26 !important;
        }
        .lz-worker-form-page .lz-phone-grid .form-label {
            font-size: 0.78rem;
            color: #7a8582;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-worker-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-user-edit"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <div class="lz-page-actions">
                <a href="{{ $menu_link ?? route('center_user.workers.index') }}" class="btn btn-luzori-muted btn-sm px-3 py-2">
                    <i class="ti ti-arrow-left me-1"></i>{{ __('general.back') }}
                </a>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="branch_id" class="lz-field-label">{{ __('field.branch') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.select_a_branch_from_the_list') }}</small>
                            <select class="select2 form-control" name="branch_id" id="branch_id">
                                @foreach ($branches as $branch)
                                    <option
                                        {{ $item ? ($item->branch_id == $branch->id ? 'selected' : null) : null }}
                                        value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="shift_id" class="lz-field-label">{{ __('field.shift') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.select_a_shift_from_the_list') }}</small>
                            <select class="select2 form-control" name="shift_id" id="shift_id">
                                @foreach ($shifts as $shift)
                                    <option
                                        {{ $item ? ($item->shift_id == $shift->id ? 'selected' : null) : null }}
                                        value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label for="services" class="lz-field-label">{{ __('field.services') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.select_a_service_from_the_list') }}</small>
                            <select class="select2 form-control" name="services[]" id="services" multiple>
                                @foreach ($services as $service)
                                    <option
                                        {{ $item ? (in_array($service->id, $item->services->pluck('service_id')->toArray()) ? 'selected' : null) : null }}
                                        value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="lz-field-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_full_name_of_the_employee') }}</small>
                            <input type="text" id="name" class="form-control" name="name"
                                placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : null }}" />
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="lz-field-label">{{ __('field.email') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_email_of_the_employee') }}</small>
                            <input type="email" id="email" class="form-control" name="email"
                                placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : null }}" />
                        </div>

                        <div class="col-md-4">
                            <label for="salary" class="lz-field-label">{{ __('field.salary') }}</label>
                            <small class="lz-field-help">{{ __('general.enter_the_salary_of_the_employee') }}</small>
                            <input type="number" id="salary" class="form-control" name="salary"
                                placeholder="{{ __('field.salary') }}" value="{{ $item ? $item->salary : null }}" />
                        </div>

                        <div class="col-md-4">
                            <label for="percentage" class="lz-field-label">{{ __('field.percentage') }}</label>
                            <small class="lz-field-help">{{ __('general.enter_the_percentage_of_the_employee') }}</small>
                            <input type="number" id="percentage" class="form-control" name="percentage"
                                step="0.01" min="0" max="100"
                                placeholder="{{ __('field.percentage') }}"
                                value="{{ $item ? $item->percentage : 0 }}"
                                {{ empty($canManagePercentage) ? 'readonly' : '' }} />
                        </div>

                        <div class="col-md-4">
                            <label class="lz-field-label">{{ __('field.is_professional') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">&nbsp;</small>
                            <div class="lz-switch-wrap">
                                <div class="form-check form-switch form-check-primary mb-0">
                                    <input type="hidden" name="is_professional" value="0">
                                    <input type="checkbox" class="form-check-input" name="is_professional" id="is_professional" value="1" {{ $item && $item->is_professional == '1' ? 'checked' : null }}>
                                    <label class="form-check-label" for="is_professional">
                                        {{ __('field.is_professional') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="visa_start_date" class="lz-field-label">{{ __('field.visa_start_date') }}</label>
                            <small class="lz-field-help">{{ __('general.enter_the_visa_start_date_of_the_employee') }}</small>
                            <input type="date" id="visa_start_date" class="form-control" name="visa_start_date"
                                value="{{ $item ? $item->visa_start_date : null }}" />
                        </div>

                        <div class="col-md-6">
                            <label for="visa_end_date" class="lz-field-label">{{ __('field.visa_end_date') }}</label>
                            <small class="lz-field-help">{{ __('general.enter_the_visa_end_date_of_the_employee') }}</small>
                            <input type="date" id="visa_end_date" class="form-control" name="visa_end_date"
                                value="{{ $item ? $item->visa_end_date : null }}" />
                        </div>

                        <div class="col-md-12">
                            <label for="phone" class="lz-field-label">{{ __('field.phone') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_phone_number_of_the_employee') }}</small>
                            <div class="row g-2 lz-phone-grid">
                                <div class="col-md-2">
                                    @include('Admin.Components.country_code', ['item' => $item])
                                </div>
                                <div class="col-md-2" id="phone_prefix_container" style="display: {{ ($item && $item->country_code == '+971') ? 'block' : 'none' }};">
                                    <label class="form-label">Prefix</label>
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
                                <div class="col-md-{{ ($item && $item->country_code == '+971') ? '8' : '10' }}" id="phone_input_container">
                                    <label class="form-label">&nbsp;</label>
                                    <input type="tel" id="phone" class="form-control" name="phone"
                                        placeholder="{{ __('field.phone') }}" maxlength="7"
                                        value="{{ $phoneWithoutPrefix }}" required />
                                    <div id="phone-invalid-feedback" class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            @include('CenterUser.Components.image', [
                                'item' => $item,
                                'name' => 'image',
                                'model' => 'worker',
                            ])
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ $menu_link ?? route('center_user.workers.index') }}" class="btn btn-luzori-beige">
                        {{ __('general.close') }}
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
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

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
                    phoneInputContainer.classList.remove('col-md-10');
                    phoneInputContainer.classList.add('col-md-8');
                } else {
                    phonePrefixContainer.style.display = 'none';
                    phoneInputContainer.classList.remove('col-md-8');
                    phoneInputContainer.classList.add('col-md-10');
                }
            }

            togglePhonePrefix();

            if (countryCodeSelect) {
                countryCodeSelect.addEventListener('change', togglePhonePrefix);
            }

            function validatePhone() {
                if (!phoneInput) return true;
                const value = (phoneInput.value || '').trim();
                const isValid = value.length === 7 && /^[0-9]+$/.test(value);
                const feedbackEl = document.getElementById('phone-invalid-feedback');
                if (!isValid) {
                    phoneInput.classList.remove('is-valid');
                    phoneInput.classList.add('is-invalid');
                    if (feedbackEl) feedbackEl.textContent = value.length > 0 ? '{{ __('field.phone_must_be_7_digits') }}' : '';
                } else {
                    phoneInput.classList.remove('is-invalid');
                    phoneInput.classList.add('is-valid');
                    if (feedbackEl) feedbackEl.textContent = '';
                }
                return isValid;
            }

            if (phoneInput) {
                phoneInput.addEventListener('input', validatePhone);
                phoneInput.addEventListener('keyup', validatePhone);
                phoneInput.addEventListener('blur', validatePhone);
            }

            const form = document.getElementById('frmSubmit');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validatePhone()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    if (countryCodeSelect && countryCodeSelect.value === '+971' && phonePrefixSelect && phoneInput) {
                        const prefix = phonePrefixSelect.value;
                        const phone = phoneInput.value;
                        if (prefix && phone) {
                            phoneInput.value = prefix + phone;
                        }
                    }
                });
            }
        });
    </script>

    @include('CenterUser.Components.submit-form-ajax')
    @include('CenterUser.Components.image-js')
@endsection
