@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
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
                        <div class="container">
                            <div class="row">
                                <div class="col-md-2">
                                    @include('Admin.Components.country_code', ['item' => $item])
                                </div>
                                <div class="col-md-2" id="phone_prefix_container" style="display: {{ ($item && $item->country_code == '+971') ? 'block' : 'none' }};">
                                    <div class="mb-4">
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
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.phone')}} <small class="text-muted">{{__('general.enter_the_phone_number')}}</small> <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="phone" id="phone" value="{{ $phoneWithoutPrefix }}"
                                            placeholder="5XXXXXXX" 
                                            required
                                            maxlength="7"
                                            pattern="5[0-9]{7}"
                                            title="Phone number must start with 5 and be exactly 8 digits" />
                                    </div>
                                </div>
                                <div class="col-md-{{ ($item && $item->country_code == '+971') ? '4' : '6' }}" id="email_input_container">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.email')}} <small class="text-muted">{{__('general.enter_the_email_address')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="email" value="{{ $item->email }}"
                                            placeholder="{{__('field.email')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.facebook')}} <small class="text-muted">{{__('general.enter_the_facebook_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="facebook" value="{{ $item->facebook }}"
                                            placeholder="{{__('field.facebook')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.instagram')}} <small class="text-muted">{{__('general.enter_the_instagram_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="instagram" value="{{ $item->instagram }}"
                                            placeholder="{{__('field.instagram')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.twitter')}} <small class="text-muted">{{__('general.enter_the_twitter_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="twitter" value="{{ $item->twitter }}"
                                            placeholder="{{__('field.twitter')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.whatsapp')}} <small class="text-muted">{{__('general.enter_the_whatsapp_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="whatsapp" value="{{ $item->whatsapp }}"
                                            placeholder="{{__('field.whatsapp')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.youtube')}} <small class="text-muted">{{__('general.enter_the_youtube_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="youtube" value="{{ $item->youtube }}"
                                            placeholder="{{__('field.youtube')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.linkedin')}} <small class="text-muted">{{__('general.enter_the_linkedin_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="linkedin" value="{{ $item->linkedin }}"
                                            placeholder="{{__('field.linkedin')}}" />
                                    </div>
                                </div>
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
    <script>
        // Phone prefix toggle for UAE (+971)
        document.addEventListener('DOMContentLoaded', function() {
            const countryCodeSelect = document.querySelector('select[name="country_code"]');
            const phonePrefixContainer = document.getElementById('phone_prefix_container');
            const phoneInputContainer = document.getElementById('phone_input_container');
            const phoneInput = document.getElementById('phone');
            const phonePrefixSelect = document.getElementById('phone_prefix');

            const emailInputContainer = document.getElementById('email_input_container');
            
            function togglePhonePrefix() {
                if (countryCodeSelect && countryCodeSelect.value === '+971') {
                    phonePrefixContainer.style.display = 'block';
                    phoneInputContainer.classList.remove('col-md-4');
                    phoneInputContainer.classList.add('col-md-2');
                    if (emailInputContainer) {
                        emailInputContainer.classList.remove('col-md-6');
                        emailInputContainer.classList.add('col-md-4');
                    }
                } else {
                    phonePrefixContainer.style.display = 'none';
                    phoneInputContainer.classList.remove('col-md-2');
                    phoneInputContainer.classList.add('col-md-4');
                    if (emailInputContainer) {
                        emailInputContainer.classList.remove('col-md-4');
                        emailInputContainer.classList.add('col-md-6');
                    }
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
