@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="branch_id" class="form-label">{{ __('field.branch') }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_branch_from_the_list')}}</small>
                                    <select class="select2 form-control" name="branch_id" id="branch_id">
                                        @foreach ($branches as $branch)
                                            <option
                                                {{ $item ? ($item->branch_id == $branch->id ? 'selected' : null) : null }}
                                                value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="services" class="form-label">{{ __('field.services') }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_service_from_the_list')}}</small>
                                    <select class="select2 form-control" name="services[]" id="services" multiple>
                                        @foreach ($services as $service)
                                            <option
                                                {{ $item ? (in_array($service->id, $item->services->pluck('service_id')->toArray()) ? 'selected' : null) : null }}
                                                value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="shift_id" class="form-label">{{ __('field.shift') }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_shift_from_the_list')}}</small>
                                    <select class="select2 form-control" name="shift_id" id="shift_id">
                                        @foreach ($shifts as $shift)
                                            <option
                                                {{ $item ? ($item->shift_id == $shift->id ? 'selected' : null) : null }}
                                                value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="name" class="form-label">{{ __('field.name') }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_full_name_of_the_employee')}}</small>
                                    <input type="text" id="name" class="form-control" name="name"
                                        placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : null }}" />
                                    <small class="text-muted">Enter the full name of the employee.</small>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="email" class="form-label">{{ __('field.email') }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_email_of_the_employee')}}</small>
                                    <input type="email" id="email" class="form-control" name="email"
                                        placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : null }}" />
                                    <small class="text-muted">Used for login and notifications.</small>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="phone" class="form-label">{{ __('field.phone') }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_phone_number_of_the_employee')}}</small>
                                    <div class="row">
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
                                            <div class="invalid-feedback" id="phone-invalid-feedback"></div>
                                        </div>
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
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-sm submitFrom">
                            <i class="menu-icon tf-icons ti ti-check"></i>
                            <span>{{ __('general.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
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
                phoneInput.addEventListener('blur', validatePhone);
            }

            const form = document.getElementById('phone');
            if (form) {
                form.addEventListener('submit', function(e) {
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
