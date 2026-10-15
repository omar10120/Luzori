@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons/css/flag-icons.min.css">
    @include('CenterUser.Components.settings-form-styles')
    <style>
        .lz-back-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #0a3a34;
            color: #fff !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(0, 43, 38, 0.18);
        }
        .lz-back-link:hover { filter: brightness(1.08); color: #fff !important; }

        .lz-form-card .form-check-input {
            border-color: #c5a070;
            width: 1.1em;
            height: 1.1em;
        }
        .lz-form-card .form-check-input:checked {
            background-color: #002b26;
            border-color: #002b26;
        }
        .lz-form-card .form-check-label {
            color: #002b26;
            font-weight: 600;
        }

        /* Luzori image upload look (page-scoped) */
        .lz-form-card .image-upload-dropzone {
            width: 100% !important;
            min-height: 160px;
            border: 2px dashed #b7c9c4 !important;
            border-radius: 16px !important;
            background: #eef5f3 !important;
            padding: 2.5rem 1.5rem !important;
        }
        .lz-form-card .image-upload-dropzone:hover {
            background: #e5f0ed !important;
        }
        .lz-form-card .image-upload-dropzone.dragover {
            background: #dceae6 !important;
            border-color: #002b26 !important;
        }
        .lz-form-card .dropzone-icon i {
            color: #0a3a34 !important;
            font-size: 2.75rem !important;
        }
        .lz-form-card .dropzone-text {
            color: #0a3a34 !important;
            font-weight: 600 !important;
        }
        .lz-form-card .dropzone-text::after {
            content: " PNG, JPG (Max 5MB)";
            display: block;
            margin-top: 0.25rem;
            font-size: 0.8rem;
            font-weight: 500;
            color: #7a8582;
        }
        .lz-form-card .image-preview-wrapper {
            border-radius: 14px;
            overflow: hidden;
        }
        .lz-form-card .image-upload-container .form-label,
        .lz-form-card .image-upload-container > .mb-1 > .form-label {
            display: block;
            font-weight: 700;
            color: #002b26;
            margin-bottom: 0.45rem;
            font-size: 0.92rem;
        }
        /* Image component wraps label outside container in some cases */
        .lz-form-card .mb-1 > .form-label {
            font-weight: 700;
            color: #002b26;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-settings-page lz-service-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <a href="{{ route('center_user.services.index') }}" class="lz-back-link" title="{{ __('general.back') ?? 'Back' }}">
                    <i class="ti ti-arrow-left"></i>
                </a>
                <div>
                    <div class="text-muted" style="font-size:.85rem;font-weight:600;">
                        {{ is_null($item) ? __('general.add') : __('general.edit') }}
                    </div>
                    <h1 class="lz-page-title">
                        @if (is_null($item))
                            {{ __('general.add') }} {{ __('locale.service') }}
                        @else
                            {{ __('general.edit') }} {{ __('locale.service') }}
                        @endif
                    </h1>
                </div>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    @include('CenterUser.Components.languages-tabs')

                    <div class="tab-content">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label" for="name_{{ $locale }}">
                                            {{ __('field.name') }} <span class="text-danger">*</span>
                                        </label>
                                        <small class="lz-field-help">{{ __('general.enter_the_name_of_the_service') }}</small>
                                        <input type="text" id="name_{{ $locale }}" class="form-control"
                                            name="{{ $locale }}[name]"
                                            placeholder="{{ __('field.name') }}"
                                            value="{{ $item ? $item->translate($locale)->name : '' }}" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label" for="description_{{ $locale }}">
                                            {{ __('field.description') }} <span class="text-danger">*</span>
                                        </label>
                                        <small class="lz-field-help">{{ __('general.enter_the_description_of_the_service') }}</small>
                                        <textarea id="description_{{ $locale }}" class="form-control" rows="4"
                                            name="{{ $locale }}[description]"
                                            placeholder="{{ __('field.description') }}">{{ $item ? $item->translate($locale)->description : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            @include('CenterUser.Components.category-tree-select', [
                                'categoriesJson' => $categoriesJson,
                                'selectedId' => $selectedId,
                                'selectedName' => $selectedName,
                                'name' => 'category_id',
                                'label' => __('field.category'),
                            ])
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="lz-field-label" for="rooms_no">
                                {{ __('field.rooms_no') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_number_of_rooms_of_the_service') }}</small>
                            <input type="number" id="rooms_no" class="form-control" name="rooms_no"
                                placeholder="{{ __('field.rooms_no') }}"
                                value="{{ $item ? $item->rooms_no : null }}" />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="lz-field-label" for="free_book">
                                {{ __('field.free_book') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_number_of_free_books_of_the_service') }}</small>
                            <input type="number" id="free_book" class="form-control" name="free_book"
                                placeholder="{{ __('field.free_book') }}"
                                value="{{ $item ? $item->free_book : null }}" />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="lz-field-label" for="price">
                                {{ __('field.price') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_price_of_the_service') }}</small>
                            <input type="number" id="price" class="form-control" name="price"
                                placeholder="{{ __('field.price') }}"
                                value="{{ $item ? $item->price : null }}" />
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_top" name="is_top"
                                    @checked($item?->is_top) />
                                <label class="form-check-label" for="is_top">
                                    {{ __('field.is_top') }} <span class="text-danger">*</span>
                                </label>
                            </div>
                            <small class="lz-field-help mt-1">{{ __('general.select_if_the_service_is_top') }}</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_commission"
                                    name="has_commission" @checked($item?->has_commission) />
                                <label class="form-check-label" for="has_commission">
                                    {{ __('field.commission') }} <span class="text-danger">*</span>
                                </label>
                            </div>
                            <small class="lz-field-help mt-1">{{ __('general.select_if_the_service_has_commission') }}</small>
                        </div>

                        <div class="col-md-12 mb-1">
                            @include('CenterUser.Components.image', [
                                'item' => $item,
                                'name' => 'image',
                                'model' => 'service',
                            ])
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.services.index') }}" class="btn btn-luzori-beige">
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
    @include('CenterUser.Components.submit-form-ajax')
    @include('CenterUser.Components.translation-js')
    <script>
        $(document).ready(function() {
            // Style category label to match Luzori fields if present
            $('.lz-form-card label.form-label').addClass('lz-field-label').removeClass('form-label');

            // Listeners for Name
            $('#name_en').on('input', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'name_ar');
            });
            $('#name_ar').on('input', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'name_en');
            });

            // Listeners for Description
            $('#description_en').on('input', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'description_ar');
            });
            $('#description_ar').on('input', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'description_en');
            });
        });
    </script>
@endsection
