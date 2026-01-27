@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
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
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.first_name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name"
                                        placeholder="{{__('field.first_name')}}" value="{{ $item ? $item->first_name : null }}" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.last_name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name"
                                        placeholder="{{__('field.last_name')}}" value="{{ $item ? $item->last_name : null }}" required />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.email')}} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control dt-email"
                                        placeholder="example@domain.com" value="{{ $item ? $item->email : null }}" required />
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
                                    <label class="form-label">{{__('field.mobile_number')}} <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" id="phone" maxlength="7" class="form-control"
                                        placeholder="{{__('field.mobile_number')}}" value="{{ $phoneWithoutPrefix }}" 
                                        required 
                                        pattern="[0-9]{7,15}"
                                        title="Please enter a valid phone number (7-15 digits)" />
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

const phoneInput = document.getElementById('phone');

function validatePhone() {
    if (!phoneInput) {
        return true;
    }
    const value = phoneInput.value.trim();
    const isValid = value.length === 7 && /^[0-9]+$/.test(value);
    if (!isValid) {
        phoneInput.classList.add('is-invalid');
        phoneInput.siblings('.invalid-feedback').text('{{ __('field.mobile_number') }} must be 7 digits');
    } else {
        phoneInput.classList.remove('is-invalid');
        phoneInput.siblings('.invalid-feedback').text('');
    }
    return isValid;
}
    </script>

    @include('CenterUser.Components.submit-form-ajax')
@endsection
