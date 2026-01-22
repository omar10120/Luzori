@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.name') }}</label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.domain') }}</label>
                                    <input type="text" class="form-control" name="domain"
                                        placeholder="{{ __('field.domain') }}" value="{{ $item ? $item->domain : '' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.email') }}</label>
                                    <input type="text" name="email" class="form-control dt-email"
                                        placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                @include('Admin.Components.country_code', ['item' => $item])
                            </div>
                            <div class="col-md-2" id="phone_prefix_container" style="display: {{ ($item && $item->country_code == '+971') ? 'block' : 'none' }};">
                                <div class="mb-1">
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
                            </div>
                            <div class="col-md-{{ ($item && $item->country_code == '+971') ? '2' : '4' }}" id="phone_input_container">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.mobile_number') }}</label>
                                    <input type="number" maxlength="7" name="phone" id="phone" class="form-control"
                                        placeholder="{{ __('field.mobile_number') }}"
                                        value="{{ $phoneWithoutPrefix }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.password') }}</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control" name="password" id="password"
                                            placeholder="{{ __('field.password') }}" />
                                        <span class="input-group-text cursor-pointer">
                                            <i class="ti ti-eye-off" id="togglePassword"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.confirm_password') }}</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" id="confirmPassword" class="form-control"
                                            name="password_confirmation"
                                            placeholder="{{ __('field.confirm_password') }}" />
                                        <span class="input-group-text cursor-pointer">
                                            <i class="ti ti-eye-off" id="toggleConfirmPassword"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.currency') }}</label>
                                    <select class="select2 form-control" name="currency">
                                        @php
                                            $currencies = ['AED', 'USD', 'EUR', 'GBP', 'SAR', 'EGP', 'JOD', 'IQD', 'KWD', 'OMR', 'BHD', 'QAR'];
                                        @endphp
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency }}"
                                                {{ $item && $item->currency == $currency ? 'selected' : (!$item && $currency == 'AED' ? 'selected' : '') }}>
                                                {{ $currency }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.role') }}</label>
                                    <select class="select2 form-control" name="role">
                                        @foreach ($roles as $role)
                                            <option
                                                {{ $item ? ($item->roles()?->first()?->name == $role->name ? 'selected' : null) : null }}
                                                value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.image') }}</label>
                                    <input type="file" class="form-control" id="image" name="image" />
                                </div>
                                <img id="show_image" src="{{ $item ? $item->image : '' }}"
                                    style="{{ $item ? '' : 'display:none;' }} width:200px;height:200px;margin:20px;"
                                    alt="center image" />
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary submitFrom">
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
        image.onchange = evt => {
            const [file] = image.files
            if (file) {
                document.getElementById("show_image").style.display = "block";
                show_image.src = URL.createObjectURL(file)
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');

            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                // Toggle the eye icon class
                if (this.classList.contains('ti-eye')) {
                    this.classList.remove('ti-eye');
                    this.classList.add('ti-eye-off');
                } else {
                    this.classList.remove('ti-eye-off');
                    this.classList.add('ti-eye');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
            const confirmPassword = document.querySelector('#confirmPassword');

            toggleConfirmPassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPassword.setAttribute('type', type);

                // Toggle the eye icon class
                if (this.classList.contains('ti-eye')) {
                    this.classList.remove('ti-eye');
                    this.classList.add('ti-eye-off');
                } else {
                    this.classList.remove('ti-eye-off');
                    this.classList.add('ti-eye');
                }
            });
        });

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
