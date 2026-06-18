@php
    $fontFamily = App::getLocale() == 'ar' ? 'Cairo' : 'Poppins';
@endphp
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">

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
                                    <input type="number" maxlength="12" name="phone" id="phone" class="form-control"
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
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.status') }}</label>
                                    <select class="select2 form-control" name="status" id="status_select">
                                        <option value="pending" {{ $item && $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approve" {{ ($item && $item->status == 'approve') || !$item ? 'selected' : '' }}>Approve</option>
                                        <option value="reject" {{ $item && $item->status == 'reject' ? 'selected' : '' }}>Reject</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.rate') }}</label>
                                    <select class="select2 form-control" name="rate">
                                        <option value="">None</option>
                                        <option value="recently_viewed" {{ $item && $item->rate == 'recently_viewed' ? 'selected' : '' }}>Recently Viewed</option>
                                        <option value="recommended" {{ $item && $item->rate == 'recommended' ? 'selected' : '' }}>Recommended</option>
                                        <option value="new_to" {{ $item && $item->rate == 'new_to' ? 'selected' : '' }}>New to</option>
                                        <option value="trending" {{ $item && $item->rate == 'trending' ? 'selected' : '' }}>Trending</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4" id="reject_reason_container" style="display: {{ $item && $item->status == 'reject' ? 'block' : 'none' }}">
                                <div class="mb-1">
                                    <label class="form-label">Reject Reason</label>
                                    <input type="text" class="form-control" name="reject_reason" placeholder="Reason for rejection" value="{{ $item ? $item->reject_reason : '' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">Wallet Balance</label>
                                    <input type="text" class="form-control" value="{{ $item ? $item->wallet : '0.00' }}" readonly disabled />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" name="bank_name" maxlength="40" disabled
                                        placeholder="Bank Name (max 40 chars)" value="{{ $item ? $item->bank_name : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">Admin Commission (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control" name="admin_discount" 
                                        placeholder="e.g. 10.00" value="{{ $item ? $item->admin_discount : '0.00' }}" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">Iban</label>
                                    <input type="text" class="form-control" name="iban" disabled
                                        placeholder="" value="{{ $item ? $item->iban : '' }}" />
                                </div>
                            </div>
                        
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">BankAccountHolderName</label>
                                    <input type="text"  class="form-control" name="BankAccountHolderName" disabled
                                        placeholder="" value="{{ $item ? $item->BankAccountHolderName : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">BusinessName</label>
                                    <input type="text"class="form-control" name="BusinessName" disabled
                                        placeholder="" value="{{ $item ? $item->BusinessName : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">BankAccount</label>
                                    <input type="text" class="form-control" name="BankAccount" disabled
                                        placeholder="" value="{{ $item ? $item->BankAccount : '' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">BankId</label>
                                    <input type="text" class="form-control" name="BankId" disabled
                                        placeholder="" value="{{ $item ? $item->BankId : '' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.image') }} (Logo)</label>
                                    <input type="file" class="form-control" id="image" name="image" />
                                </div>
                                <img id="show_image" src="{{ $item ? $item->getFirstMediaUrl('Center') : '' }}"
                                    style="{{ $item && $item->getFirstMediaUrl('Center') ? '' : 'display:none;' }} width:200px;height:200px;margin:20px;"
                                    alt="center logo" />
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">Primary Images <small>(up to 4)</small></label>
                                    
                                    {{-- Show existing images --}}
                                    @if($item && $item->getMedia('PrimaryImage')->count())
                                        <div class="d-flex flex-wrap gap-2 mb-2" id="existingPrimaryImages">
                                            @foreach($item->getMedia('PrimaryImage') as $media)
                                                <div class="position-relative existing-img-thumb" data-media-id="{{ $media->id }}" style="width:100px;height:100px;">
                                                    <img src="{{ $media->getUrl() }}" style="width:100%;height:100%;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                                    <button type="button" class="btn btn-sm btn-danger remove-existing-img" data-media-id="{{ $media->id }}" style="position:absolute;top:2px;right:2px;padding:1px 5px;font-size:0.65rem;line-height:1;">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- New image uploads --}}
                                    <div id="adminPrimaryImagesContainer">
                                        <div class="admin-primary-slot mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="file" class="form-control form-control-sm admin-primary-input" name="primary_image[]" accept="image/*">
                                                <img class="admin-img-preview" style="width:50px;height:50px;object-fit:cover;border-radius:4px;display:none;">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="addAdminPrimaryImage" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="ti ti-plus"></i> Add Image
                                    </button>
                                    {{-- Hidden field for images to delete --}}
                                    <input type="hidden" name="delete_primary_images" id="deletePrimaryImages" value="">
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

        // Multi Primary Image management
        $(document).ready(function() {
            let existingCount = $('#existingPrimaryImages .existing-img-thumb').length || 0;
            let newSlotCount = 1;
            const maxImages = 4;

            function updateAddButton() {
                let totalCount = ($('#existingPrimaryImages .existing-img-thumb').length || 0) + $('#adminPrimaryImagesContainer .admin-primary-slot').length;
                if (totalCount >= maxImages) {
                    $('#addAdminPrimaryImage').hide();
                } else {
                    $('#addAdminPrimaryImage').show();
                }
            }

            // Add new upload slot
            $('#addAdminPrimaryImage').click(function() {
                let totalCount = ($('#existingPrimaryImages .existing-img-thumb').length || 0) + $('#adminPrimaryImagesContainer .admin-primary-slot').length;
                if (totalCount >= maxImages) return;
                newSlotCount++;
                let html = `<div class="admin-primary-slot mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <input type="file" class="form-control form-control-sm admin-primary-input" name="primary_image[]" accept="image/*">
                        <img class="admin-img-preview" style="width:50px;height:50px;object-fit:cover;border-radius:4px;display:none;">
                        <button type="button" class="btn btn-sm btn-danger remove-admin-slot" style="padding:2px 6px;font-size:0.7rem;"><i class="ti ti-x"></i></button>
                    </div>
                </div>`;
                $('#adminPrimaryImagesContainer').append(html);
                updateAddButton();
            });

            // Remove new upload slot
            $(document).on('click', '.remove-admin-slot', function() {
                $(this).closest('.admin-primary-slot').remove();
                updateAddButton();
            });

            // Preview new image
            $(document).on('change', '.admin-primary-input', function(e) {
                let file = e.target.files[0];
                let preview = $(this).siblings('.admin-img-preview');
                if (file) {
                    preview.show().attr('src', URL.createObjectURL(file));
                } else {
                    preview.hide().attr('src', '');
                }
            });

            // Remove existing image
            $(document).on('click', '.remove-existing-img', function() {
                let mediaId = $(this).data('media-id');
                let current = $('#deletePrimaryImages').val();
                let ids = current ? current.split(',') : [];
                ids.push(mediaId);
                $('#deletePrimaryImages').val(ids.join(','));
                $(this).closest('.existing-img-thumb').fadeOut(200, function() {
                    $(this).remove();
                    updateAddButton();
                });
            });

            updateAddButton();
            $('#status_select').on('change', function() {
                if ($(this).val() == 'reject') {
                    $('#reject_reason_container').show();
                } else {
                    $('#reject_reason_container').hide();
                }
            });
        });
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
            // if (form) {
            //     form.addEventListener('submit', function(e) {
            //         if (countryCodeSelect && countryCodeSelect.value === '+971' && phonePrefixSelect && phoneInput) {
            //             const prefix = phonePrefixSelect.value;
            //             const phone = phoneInput.value;
            //             if (prefix && phone) {
            //                 // Combine prefix with phone number
            //                 phoneInput.value = prefix + phone;
            //             }
            //         }
            //     });
            // }
        });
    </script>

    @include('Admin.Components.submit-form-ajax')
@endsection
