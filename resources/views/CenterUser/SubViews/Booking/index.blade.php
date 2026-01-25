@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss']);
    <style>
        .image-upload-container {
            position: relative;
        }
        
        .image-upload-dropzone {
            border: none;
            border-radius: 8px;
            padding: 4rem 2rem;
            text-align: center;
            background-color: #f3f0ff;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .image-upload-dropzone:hover {
            background-color: #ede9ff;
        }
        
        .image-upload-dropzone.dragover {
            background-color: #e8e3ff;
            border: 2px dashed #696cff;
        }
        
        .dropzone-content {
            pointer-events: none;
        }
        
        .dropzone-icon {
            margin-bottom: 1rem;
        }
        
        .dropzone-icon i {
            font-size: 3rem;
            color: #696cff;
        }
        
        .dropzone-text {
            font-size: 1rem;
            font-weight: 500;
            color: #696cff;
            margin: 0.5rem 0 0 0;
        }
        
        .image-preview-container {
            margin-top: 0;
        }
        
        .image-preview-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .image-preview {
            width: 100%;
            max-width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .image-remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .image-remove-btn:hover {
            transform: scale(1.1);
        }

        /* Mobile responsive buttons for DataTable */
        @media (max-width: 768px) {
            .dt-action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 0.25rem;
            }
            
            .dt-action-buttons .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
                margin: 0.125rem;
                white-space: nowrap;
            }
            
            .dt-action-buttons .btn i {
                font-size: 0.875rem;
                margin-right: 0.25rem;
            }
        }

        @media (max-width: 576px) {
            .dt-action-buttons {
                flex-wrap: wrap;
                gap: 0.2rem;
            }
            
            .dt-action-buttons .btn {
                font-size: 0.65rem;
                padding: 0.2rem 0.4rem;
                margin: 0.1rem;
                line-height: 1.2;
            }
            
            .dt-action-buttons .btn i {
                font-size: 0.75rem;
                margin-right: 0.2rem;
            }
        }
    </style>
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
                        <div class="col-12 mb-4">
                            <div class="bs-stepper wizard-icons wizard-icons-example mt-2">
                                <div class="bs-stepper-header">
                                    <div class="step" data-target="#first-step">
                                        <button type="button" class="step-trigger" disabled>
                                            <span class="bs-stepper-label">{{ __('locale.services') }} <span class="text-danger">*</span></span>
                                        </button>
                                    </div>
                                    <div class="line">
                                        <i class="ti ti-chevron-right"></i>
                                    </div>
                                    <div class="step" data-target="#second-step">
                                        <button type="button" class="step-trigger" disabled>
                                            <span class="bs-stepper-icon">
                                                <span class="bs-stepper-label">{{__('field.booking_details')}} <span class="text-danger">*</span></span>
                                        </button>
                                    </div>
                                    <div class="line">
                                        <i class="ti ti-chevron-right"></i>
                                    </div>
                                    <div class="step" data-target="#third-step">
                                        <button type="button" class="step-trigger" disabled>
                                            <span class="bs-stepper-label">{{__('field.customers_details')}} <span class="text-danger">*</span></span>
                                        </button>
                                    </div>
                                    <div class="line">
                                        <i class="ti ti-chevron-right"></i>
                                    </div>
                                    <div class="step" data-target="#fourth-step">
                                        <button type="button" class="step-trigger" disabled>
                                            <span class="bs-stepper-label">{{__('field.overview')}} <span class="text-danger">*</span></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="bs-stepper-content">
                                    <div id="first-step" class="content">
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <label for="services"
                                                            class="form-label mb-0">{{ __('field.services') }} <span class="text-danger">*</span></label>
                                                        <small class="text-muted">{{__('general.select_a_services_from_the_list')}}</small>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" id="addServiceQuickBtn" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                                            <i class="ti ti-plus me-1"></i>
                                                            {{ __('general.add') }} {{ __('locale.services') }}
                                                        </button>
                                                    </div>  
                                                    <select class="select2 form-control" name="services[]" id="services"
                                                        multiple>
                                                        @foreach ($services as $service)
                                                            <option value="{{ $service->id }}">{{ $service->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex justify-content-between">
                                            <button type="button" class="btn btn-label-secondary btn-prev" disabled>
                                                <i class="ti ti-arrow-left me-sm-1"></i>
                                                <span
                                                    class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                            </button>
                                            <button type="button" class="btn btn-primary btn-next" id="nextStep1" disabled>
                                                <span
                                                    class="align-middle d-sm-inline-block d-none me-sm-1">{{ __('field.next') }}</span>
                                                <i class="ti ti-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="second-step" class="content">
                                        <div id="service-container"></div>
                                        <div class="col-12 mt-4 d-flex justify-content-between">
                                            <button type="button" class="btn btn-label-secondary btn-prev" id="prevStep2">
                                                <i class="ti ti-arrow-left me-sm-1"></i>
                                                <span
                                                    class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                            </button>
                                            <button type="button" class="btn btn-primary btn-next" id="nextStep2">
                                                <span
                                                    class="align-middle d-sm-inline-block d-none me-sm-1">{{ __('field.next') }}</span>
                                                <i class="ti ti-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="third-step" class="content">
                                        <div class="row mb-4">
                                            <div class="col-md-5">
                                                <div class="mb-1">
                                                    <label for="name" class="form-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_name_of_the_customer')}}</small>
                                                    </label>
                                                    <input type="text" id="name" class="form-control" name="full_name"
                                                        placeholder="{{ __('field.name') }}" />
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="mb-1">
                                                    <label for="mobile" class="form-label">{{ __('field.mobile') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_mobile_of_the_customer')}}</small>
                                                    </label>
                                                    <div class="d-flex">
                                                        <label class="p-2" style="background: #80808045">+971</label>
                                                        <input style="border-radius:0 1px 1px 0" type="number"
                                                            id="mobile" class="form-control" name="mobile"
                                                            placeholder="{{ __('field.mobile') }}" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="mt-4">
                                                    <button type="button" class="btn btn-primary" id="checkButton">{{__('field.check')}}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <hr />
                                            <h5>{{ __('field.discount_codes') }}</h5>
                                            <div class="row mb-4">
                                                @foreach ($discounts as $discount)
                                                    <div class="col-md-3 mb-2">
                                                        <div class="form-check"
                                                            style="width: 200px;padding: 10px;color: #fff;
                                                                                        background-color: #428bca;
                                                                                        border-color: #357ebd;text-align: center;
                                                                                        display: flex;justify-content: space-between;font-size: 14px;">
                                                            <label class="form-check-label"
                                                                for="discounts{{ $discount->id }}">
                                                                {{ $discount->code . ' [' . $discount->amount . '%]' }}
                                                            </label>
                                                            <input class="form-check-input" type="radio"
                                                                name="discount_id" data-name="discount_id" value="{{ $discount->id }}"
                                                                id="discounts{{ $discount->id }}">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div id="walletsElement"></div>

                                        <div id="membershipsElement"></div>

                                        <div id="servicesTable"></div>

                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label for="payment_type"
                                                        class="form-label">{{ __('field.payment_method') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.select_a_payment_method_from_the_list')}}</small>
                                                    <select name="payment_type" id="payment_type" class="form-control">
                                                        <option value="">{{ __('field.Choose Payment Method') }}</option>
                                                        @foreach($paymentMethods as $paymentMethod)
                                                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 mt-4 d-flex justify-content-between">
                                            <button type="button" class="btn btn-label-secondary btn-prev"
                                                id="prevStep3">
                                                <i class="ti ti-arrow-left me-sm-1"></i>
                                                <span
                                                    class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                            </button>
                                            <button type="button" class="btn btn-primary btn-next" id="nextStep3"
                                                disabled>
                                                <span
                                                    class="align-middle d-sm-inline-block d-none me-sm-1">{{ __('field.next') }}</span>
                                                <i class="ti ti-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="fourth-step" class="content">
                                        <div id="review-content"></div>
                                        
                                        <div class="col-12 mt-4 d-flex justify-content-between">
                                            <button type="button" class="btn btn-label-secondary btn-prev"
                                                id="prevStep4">
                                                <i class="ti ti-arrow-left me-sm-1"></i>
                                                <span
                                                    class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                            </button>
                                            <button type="submit" class="btn btn-success submitFrom" id="saveBooking">
                                                <i class="menu-icon tf-icons ti ti-check"></i>
                                                <span>{{ __('general.save') }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Service Quick Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">{{ __('general.add') }} {{ __('locale.services') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quick-add-service-form">
                        @csrf
                        <ul class="nav nav-tabs" role="tablist">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : null }}"
                                        id="quick-service-{{ $locale }}-tab-link" data-bs-toggle="tab"
                                        href="#quick-service-{{ $locale }}-add" aria-controls="quick-service-{{ $locale }}-add"
                                        role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        <i class="menu-icon tf-icons ti ti-flag"></i>
                                        {{ Str::upper($locale) }}</a>
                                </li>
                            @endforeach
                        </ul>
                        
                        <div class="tab-content mb-3">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="quick-service-{{ $locale }}-add"
                                    aria-labelledby="quick-service-{{ $locale }}-tab-link" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="quick_service_name_{{ $locale }}" class="form-label">
                                            {{ __('field.name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="quick_service_name_{{ $locale }}" class="form-control"
                                            name="{{ $locale }}[name]"
                                            placeholder="{{ __('field.name') }}" required />
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_service_rooms_no" class="form-label">
                                    {{ __('field.rooms_no') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="quick_service_rooms_no" class="form-control" name="rooms_no"
                                    placeholder="{{ __('field.rooms_no') }}" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quick_service_free_book" class="form-label">
                                    {{ __('field.free_book') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="quick_service_free_book" class="form-control" name="free_book"
                                    placeholder="{{ __('field.free_book') }}" required />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_service_price" class="form-label">
                                    {{ __('field.price') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="quick_service_price" class="form-control" name="price"
                                    placeholder="{{ __('field.price') }}" step="0.01" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex gap-4 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="quick_service_is_top" name="is_top" />
                                        <label class="form-check-label" for="quick_service_is_top">{{ __('field.is_top') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="quick_service_has_commission" name="has_commission" />
                                        <label class="form-check-label" for="quick_service_has_commission">{{ __('field.commission') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('field.image') }} <span class="text-danger">*</span>
                            </label>
                            <div class="image-upload-container" id="quick-service-image-container">
                                <div class="image-upload-dropzone" id="quick-service-dropzone">
                                    <input type="file" class="d-none" id="quick_service_image" name="image" accept="image/*" required />
                                    <div class="dropzone-content">
                                        <div class="dropzone-icon">
                                            <i class="ti ti-camera-plus"></i>
                                        </div>
                                        <p class="dropzone-text">Add a photo</p>
                                    </div>
                                </div>
                                <div class="image-preview-container" id="quick-service-preview-container" style="display: none;">
                                    <div class="image-preview-wrapper">
                                        <img id="quick-service-preview-img" class="image-preview" src="" alt="Service image" />
                                        <button type="button" class="btn btn-sm btn-danger image-remove-btn" id="quick-service-remove-btn">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="quick-service-image-error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-quick-service-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js']);
@endsection

@section('page-script') 
    @vite(['resources/assets/js/forms-selects.js', 'resources/assets/js/app-ecommerce-product-add.js', 'resources/assets/js/form-wizard-icons.js']);

    @include('CenterUser.Components.submit-form-ajax')
    @include('CenterUser.Components.wizard')

    <script>
        $(document).ready(function() {
            // Quick Add Service Modal Image Handling
            const quickServiceImageInput = document.getElementById('quick_service_image');
            const quickServiceDropzone = document.getElementById('quick-service-dropzone');
            const quickServicePreviewContainer = document.getElementById('quick-service-preview-container');
            const quickServicePreviewImg = document.getElementById('quick-service-preview-img');
            const quickServiceRemoveBtn = document.getElementById('quick-service-remove-btn');

            if (quickServiceDropzone && quickServiceImageInput) {
                // Click to select file
                quickServiceDropzone.addEventListener('click', function() {
                    quickServiceImageInput.click();
                });

                // File input change
                quickServiceImageInput.addEventListener('change', function(e) {
                    handleQuickServiceFile(e.target.files[0]);
                });

                // Drag and drop handlers
                quickServiceDropzone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    quickServiceDropzone.classList.add('dragover');
                });

                quickServiceDropzone.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    quickServiceDropzone.classList.remove('dragover');
                });

                quickServiceDropzone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    quickServiceDropzone.classList.remove('dragover');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        const file = files[0];
                        if (file.type.startsWith('image/')) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            quickServiceImageInput.files = dataTransfer.files;
                            handleQuickServiceFile(file);
                        }
                    }
                });

                // Handle file preview
                function handleQuickServiceFile(file) {
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            quickServicePreviewImg.src = e.target.result;
                            quickServicePreviewContainer.style.display = 'block';
                            quickServiceDropzone.style.display = 'none';
                        };
                        reader.readAsDataURL(file);
                    }
                }

                // Remove image
                if (quickServiceRemoveBtn) {
                    quickServiceRemoveBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        quickServiceImageInput.value = '';
                        quickServicePreviewImg.src = '';
                        quickServicePreviewContainer.style.display = 'none';
                        quickServiceDropzone.style.display = 'flex';
                    });
                }
            }

            // Quick Add Service Form Submission
            $('#save-quick-service-btn').on('click', function(e) {
                e.preventDefault();
                
                const form = $('#quick-add-service-form')[0];
                
                // Clear previous errors
                $('.invalid-feedback').text('');
                $('.form-control, .form-check-input').removeClass('is-invalid');
                $('#quick-service-image-container').removeClass('is-invalid');
                
                // Client-side validation
                let isValid = true;
                let firstErrorField = null;
                
                // Validate name fields for all locales
                @foreach (Config::get('translatable.locales') as $locale)
                    const nameField{{ $loop->index }} = $('#quick_service_name_{{ $locale }}');
                    if (!nameField{{ $loop->index }}.val() || nameField{{ $loop->index }}.val().trim() === '') {
                        nameField{{ $loop->index }}.addClass('is-invalid');
                        nameField{{ $loop->index }}.siblings('.invalid-feedback').text('{{ __('field.name') }} is required');
                        isValid = false;
                        if (!firstErrorField) firstErrorField = nameField{{ $loop->index }};
                    }
                @endforeach
                
                // Validate rooms_no
                const roomsNoField = $('#quick_service_rooms_no');
                if (!roomsNoField.val() || roomsNoField.val().trim() === '') {
                    roomsNoField.addClass('is-invalid');
                    roomsNoField.siblings('.invalid-feedback').text('{{ __('field.rooms_no') }} is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = roomsNoField;
                }
                
                // Validate free_book
                const freeBookField = $('#quick_service_free_book');
                if (!freeBookField.val() || freeBookField.val().trim() === '') {
                    freeBookField.addClass('is-invalid');
                    freeBookField.siblings('.invalid-feedback').text('{{ __('field.free_book') }} is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = freeBookField;
                }
                
                // Validate price
                const priceField = $('#quick_service_price');
                if (!priceField.val() || priceField.val().trim() === '') {
                    priceField.addClass('is-invalid');
                    priceField.siblings('.invalid-feedback').text('{{ __('field.price') }} is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = priceField;
                }
                
                // Validate image
                const imageField = $('#quick_service_image');
                if (!imageField[0].files || imageField[0].files.length === 0) {
                    $('#quick-service-image-container').addClass('is-invalid');
                    $('#quick-service-image-error').text('{{ __('field.image') }} is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = imageField;
                }
                
                if (!isValid) {
                    // Scroll to first error field
                    if (firstErrorField) {
                        $('html, body').animate({
                            scrollTop: firstErrorField.offset().top - 100
                        }, 500);
                    }
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please fill all required fields');
                    }
                    return false;
                }
                
                const formData = new FormData(form);
                formData.append('quick_add', '1'); // Flag for quick add

                // Disable button and show loading
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.services.updateOrCreate") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.message === 'redirect_to_home' || response.service) {
                            // Get service data from response
                            const serviceData = response.service || response.data;
                            
                            // Add to services dropdown
                            const $servicesSelect = $('#services');
                            const newOption = new Option(serviceData.name, serviceData.id, false, false);
                            $servicesSelect.append(newOption).trigger('change');

                            // Update servicesData object
                            if (typeof servicesData !== 'undefined') {
                                servicesData[serviceData.id] = {
                                    id: serviceData.id,
                                    name: serviceData.name,
                                    price: serviceData.price || 0,
                                    has_commission: serviceData.has_commission || false
                                };
                            }

                            // Close modal and reset form
                            $('#addServiceModal').modal('hide');
                            $('#quick-add-service-form')[0].reset();
                            quickServicePreviewContainer.style.display = 'none';
                            quickServiceDropzone.style.display = 'flex';
                            quickServicePreviewImg.src = '';

                            // Show success message
                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __('admin.operation_done_successfully') }}');
                            }

                            // Auto-select the newly added service
                            $servicesSelect.val(serviceData.id).trigger('change');
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || '{{ __('admin.an_error_occurred') }}');
                            }
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            let firstErrorField = null;
                            
                            $.each(errors, function(key, value) {
                                // Handle nested locale fields (e.g., ar.name, en.name)
                                if (key.includes('.')) {
                                    const parts = key.split('.');
                                    const locale = parts[0];
                                    const fieldName = parts[1];
                                    const $field = $('#quick_service_name_' + locale);
                                    if ($field.length) {
                                        $field.addClass('is-invalid');
                                        $field.siblings('.invalid-feedback').text(value[0]);
                                        if (!firstErrorField) firstErrorField = $field;
                                    }
                                } else {
                                    // Handle regular fields
                                    const fieldId = 'quick_service_' + key.replace(/_/g, '_');
                                    let $field = $('#' + fieldId);
                                    
                                    // Special handling for image field
                                    if (key === 'image') {
                                        $('#quick-service-image-container').addClass('is-invalid');
                                        $('#quick-service-image-error').text(value[0]);
                                        if (!firstErrorField) firstErrorField = $('#quick_service_image');
                                    } else if ($field.length) {
                                        $field.addClass('is-invalid');
                                        $field.siblings('.invalid-feedback').text(value[0]);
                                        if (!firstErrorField) firstErrorField = $field;
                                    }
                                }
                            });
                            
                            // Scroll to first error field
                            if (firstErrorField) {
                                $('html, body').animate({
                                    scrollTop: firstErrorField.offset().top - 100
                                }, 500);
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                            }
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Reset form when modal is closed
            $('#addServiceModal').on('hidden.bs.modal', function() {
                $('#quick-add-service-form')[0].reset();
                $('.invalid-feedback').text('');
                $('.form-control, .form-check-input').removeClass('is-invalid');
                $('#quick-service-image-container').removeClass('is-invalid');
                if (quickServicePreviewContainer) {
                    quickServicePreviewContainer.style.display = 'none';
                }
                if (quickServiceDropzone) {
                    quickServiceDropzone.style.display = 'flex';
                }
                if (quickServicePreviewImg) {
                    quickServicePreviewImg.src = '';
                }
            });
            
            // Clear image error when image is selected
            if (quickServiceImageInput) {
                quickServiceImageInput.addEventListener('change', function() {
                    $('#quick-service-image-container').removeClass('is-invalid');
                    $('#quick-service-image-error').text('');
                });
            }
        });
    </script>
@endsection
