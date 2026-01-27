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
                @if($item)
                    <input type="hidden" name="id" value="{{ $item->id }}">
                @endif
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : '' }}" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.email') }}@if(!$item) <span class="text-danger">*</span>@endif</label>
                                    <input type="text" name="email" class="form-control dt-email"
                                        placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : '' }}" {{ !$item ? 'required' : '' }} />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label for="branch_id" class="form-label">{{ __('field.branch') }} <span class="text-danger">*</span></label>
                                    <select {{ $item ? 'disabled' : '' }} class="select2 form-control" name="branch_id" required>
                                        @foreach ($branches as $branch)
                                            <option {{ $item ? ($item->branch_id == $branch->id ? 'selected' : null) : null }}
                                                value="{{ $branch->id }}">{{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($item)
                                        <input type="hidden" name="branch_id" value="{{ $item->branch_id }}">
                                    @endif
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
                                    <label class="form-label">{{ __('field.phone') }} <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" id="phone" class="form-control" maxlength="7"
                                        placeholder="{{ __('field.phone') }}" value="{{ $phoneWithoutPrefix }}" required />
                                    
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.password') }}@if(!$item) <span class="text-danger">*</span>@endif</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control" name="password"
                                            placeholder="{{ __('field.password') }}" {{ !$item ? 'required' : '' }} />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.confirm_password') }}@if(!$item) <span class="text-danger">*</span>@endif</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control" name="password_confirmation"
                                            placeholder="{{ __('field.confirm_password') }}" {{ !$item ? 'required' : '' }} />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.role') }} <span class="text-danger">*</span></label>
                                    <select {{ $item ? 'disabled' : '' }} class="select2 form-control" name="role" required>
                                        @foreach ($roles as $role)
                                            <option
                                                {{ $item ? ($item->roles()?->first()?->name == $role->name ? 'selected' : null) : null }}
                                                value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($item)
                                        <input type="hidden" name="role" value="{{ $item->roles()?->first()?->id }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.image') }}</label>
                                    <input type="file" class="form-control" id="image" name="image" />
                                </div>
                                @php
                                    $userImage = $item && $item->image ? $item->image : asset('assets/img/avatars/1.png');
                                @endphp
                                <img id="show_image" src="{{ $userImage }}"
                                    style="width:200px;height:200px;margin:20px;"
                                    alt="center user image" />
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

        // Phone prefix toggle for UAE (+971)
        document.addEventListener('DOMContentLoaded', function() {
            const countryCodeSelect = document.querySelector('select[name="country_code"]');
            const phonePrefixContainer = document.getElementById('phone_prefix_container');
            const phoneInputContainer = document.getElementById('phone_input_container');
            const phoneInput = document.getElementById('phone');
            const phonePrefixSelect = document.getElementById('phone_prefix');
            const form = document.getElementById('frmSubmit');

            function validatePhone() {
                if (!phoneInput) {
                    return true;
                }
                const value = phoneInput.value.trim();
                const isValid = value.length === 7 && /^[0-9]+$/.test(value);
                if (!isValid) {
                    phoneInput.classList.add('is-invalid');
                } else {
                    phoneInput.classList.remove('is-invalid');
                }
                return isValid;
            }

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

            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validatePhone()) {
                        e.preventDefault();
                        e.stopPropagation();
                        return;
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

            if (phoneInput) {
                phoneInput.addEventListener('input', validatePhone);
                phoneInput.addEventListener('keyup', validatePhone);
                phoneInput.addEventListener('blur', validatePhone);
            }
        });
    </script>

    @include('CenterUser.Components.submit-form-ajax')
@endsection
