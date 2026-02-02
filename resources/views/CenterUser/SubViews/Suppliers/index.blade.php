@extends('layouts/layoutMaster')

@section('title', $title ?? __('locale.suppliers'))

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
@endsection

@section('content')

    <div class="container">
        @include('CenterUser.Components.breadcrumbs')
        

        @if (\Session::has('success'))
            <div class="alert alert-success">
                <div>{!! \Session::get('success') !!}</div>
            </div>
        @endif
        @if (\Session::has('error'))
            <div class="alert alert-danger">
                <div>{!! \Session::get('error') !!}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title ?? __('locale.suppliers') }}</h2>
                    </div>
                    <div class="card-body">
                        @if(isset($dataTable))
                            {!! $dataTable->table(['class' => 'table table-bordered table-hover']) !!}
                        @else
                            <!-- Create/Edit Form -->
                            <form method="POST" action="{{ $requestUrl }}" enctype="multipart/form-data">
                                @csrf
                                @if($item)
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    @if($item->logo)
                                        <input type="hidden" name="old_logo" value="{{ $item->logo }}">
                                    @endif
                                @endif
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-3">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                                            <small class="text-muted d-block mb-2">{{__('general.enter_the_name_of_the_supplier')}}</small>
                                            <input type="text" class="form-control" name="name"
                                                placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : '' }}" required />
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.email') }} <span class="text-danger">*</span></label>
                                            <small class="text-muted d-block mb-2">{{__('general.enter_the_email_address_of_the_supplier')}}</small>
                                            <input type="email" class="form-control" name="email"
                                                placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : '' }}" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-3">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.phone_number') }} <span class="text-danger">*</span></label>
                                            <small class="text-muted d-block mb-2">{{__('general.enter_the_phone_number_of_the_supplier')}}</small>
                                            <div class="row g-2">
                                                <div class="col-12 col-sm-12 col-md-5 col-lg-4">
                                                    @include('Admin.Components.country_code', ['item' => $item])
                                                </div>
                                                <div class="col-12 col-sm-6 col-md-3 col-lg-2" id="phone_prefix_container" style="display: {{ ($item && $item->country_code == '+971') ? 'block' : 'none' }};">
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
                                                <div class="col-12 col-sm-{{ ($item && $item->country_code == '+971') ? '6' : '12' }} col-md-{{ ($item && $item->country_code == '+971') ? '4' : '7' }} col-lg-{{ ($item && $item->country_code == '+971') ? '6' : '8' }}" id="phone_input_container">
                                                    <label class="form-label">&nbsp;</label>
                                                    <input type="tel" id="phone" class="form-control" name="phone"
                                                        placeholder="{{ __('field.phone_number') }}" maxlength="7"
                                                        value="{{ $phoneWithoutPrefix }}" required />
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.logo') }}</label>
                                            <small class="text-muted d-block mb-2">{{__('general.select_the_logo_of_the_supplier')}}</small>
                                            <input type="file" class="form-control" name="logo" accept="image/*" id="logoInput" />
                                            <div class="mt-2 d-flex align-items-center gap-2">
                                                @if($item && $item->logo)
                                                    <img src="{{ $item->logo_url ?: asset('storage/' . $item->logo) }}" alt="Current Logo" class="img-thumbnail" id="currentLogo" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Default Logo" class="img-thumbnail" id="defaultLogo" style="display: none; width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Default Logo" class="img-thumbnail" id="defaultLogo" style="width: 60px; height: 60px; object-fit: cover;">
                                                @endif
                                            </div>
                                            <div class="mt-2" id="logoPreview" style="display: none;">
                                                <img id="previewImg" alt="Logo Preview" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-12">
                                        @include('Admin.Components.languages-tabs')
                                        <div class="tab-content">
                                            @foreach (Config::get('translatable.locales') as $locale)
                                                <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                                    aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <div class="mb-1">
                                                                <label class="form-label">{{ __('field.description') }} ({{ strtoupper($locale) }}) <span class="text-danger">*</span></label>
                                                                <small class="text-muted d-block mb-2">{{__('general.enter_the_description_of_the_supplier')}}</small>
                                                                <textarea name="{{ $locale }}[description]" id="description_{{ $locale }}" class="form-control" rows="3"
                                                                    placeholder="{{ __('field.description') }}">{{ $item ? $item->translate($locale)->description : '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary submitFrom">
                                            <i class="menu-icon tf-icons ti ti-check"></i> {{ __('general.save') }}
                                        </button>
                                        <a href="{{ route('center_user.suppliers.index') }}" class="btn btn-secondary">
                                            <i class="ti ti-arrow-left"></i> {{ __('general.back') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @if(isset($dataTable))
        @include('CenterUser.Components.datatable-js')
        
        <script>
            // Handle description modal functionality
            $(document).on('click', '[data-bs-target^="#descModal"]', function() {
                var modalId = $(this).data('bs-target').replace('#descModal', '');
                var descText = $(this).data('description');
                
                // Check if modal already exists
                if ($('#descModal' + modalId).length === 0) {
                    // Create modal dynamically
                    var modalHtml = `
                        <div class="modal fade" id="descModal${modalId}" tabindex="-1" aria-labelledby="descModalLabel${modalId}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="descModalLabel${modalId}">Description</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p id="descContent${modalId}">${descText}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('body').append(modalHtml);
                } else {
                    // Update existing modal content
                    $('#descContent' + modalId).text(descText);
                }
                
                // Show the modal using Bootstrap 5
                var modal = new bootstrap.Modal(document.getElementById('descModal' + modalId));
                modal.show();
            });
        </script>
    @else
        @include('Admin.Components.image-js')
        @include('CenterUser.Components.translation-js')
        @include('CenterUser.Components.submit-form-ajax')
        
        <style>
            @media (max-width: 576px) {
                .row.g-2 > div {
                    margin-bottom: 0.75rem;
                }
                #phone_prefix_container {
                    margin-top: 0;
                }
                #phone_input_container {
                    margin-top: 0;
                }
            }
            select[name="country_code"] {
                width: 100%;
                min-width: 0;
            }
            select[name="country_code"] option {
                white-space: normal;
                padding: 0.5rem;
            }
            .form-label {
                font-weight: 500;
                margin-bottom: 0.25rem;
                display: block;
            }
            .text-muted {
                font-size: 0.875rem;
                display: block;
                margin-bottom: 0.5rem;
            }
            .row.g-2 {
                margin-left: -0.25rem;
                margin-right: -0.25rem;
            }
            .row.g-2 > div {
                padding-left: 0.25rem;
                padding-right: 0.25rem;
            }
            .img-thumbnail {
                border-radius: 0.375rem;
            }
        </style>
        
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
                        phoneInputContainer.classList.remove('col-sm-12', 'col-md-7', 'col-lg-8');
                        phoneInputContainer.classList.add('col-sm-6', 'col-md-4', 'col-lg-6');
                    } else {
                        phonePrefixContainer.style.display = 'none';
                        phoneInputContainer.classList.remove('col-sm-6', 'col-md-4', 'col-lg-6');
                        phoneInputContainer.classList.add('col-sm-12', 'col-md-7', 'col-lg-8');
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
                    const feedbackEl = phoneInput.nextElementSibling;
                    if (!isValid) {
                        phoneInput.classList.remove('is-valid');
                        phoneInput.classList.add('is-invalid');
                        if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                            feedbackEl.textContent = value.length > 0 ? '{{ __('field.phone_must_be_7_digits') }}' : '';
                        }
                    } else {
                        phoneInput.classList.remove('is-invalid');
                        phoneInput.classList.add('is-valid');
                        if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                            feedbackEl.textContent = '';
                        }
                    }
                    return isValid;
                }

                if (phoneInput) {
                    phoneInput.addEventListener('input', validatePhone);
                    phoneInput.addEventListener('keyup', validatePhone);
                    phoneInput.addEventListener('blur', validatePhone);
                }

                const form = document.querySelector('form[method="POST"]');
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

            // Logo preview
            if (document.getElementById('logoInput')) {
                document.getElementById('logoInput').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('previewImg').src = e.target.result;
                            document.getElementById('logoPreview').style.display = 'block';
                            if (document.getElementById('currentLogo')) {
                                document.getElementById('currentLogo').style.display = 'none';
                            }
                            if (document.getElementById('defaultLogo')) {
                                document.getElementById('defaultLogo').style.display = 'none';
                            }
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // If file input is cleared, show default logo
                        document.getElementById('logoPreview').style.display = 'none';
                        if (document.getElementById('currentLogo')) {
                            document.getElementById('currentLogo').style.display = 'none';
                        }
                        if (document.getElementById('defaultLogo')) {
                            document.getElementById('defaultLogo').style.display = 'block';
                        }
                    }
                });
            }
        </script>
    @endif
@endsection
