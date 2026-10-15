@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.settings-form-styles')
    <style>
        .note-editable {
            background: #FFF;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="luzori-page lz-settings-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-files"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    @include('Admin.Components.languages-tabs')
                    <div class="tab-content pt-2">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="lz-field-label">{{ __('field.about_us') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_about_us') }}</small>
                                        <input type="hidden" name="{{ $locale }}[about_us][key]"
                                            value="{{ App\Enums\PageEnum::AboutUs->value }}">
                                        <textarea class="summernote" name="{{ $locale }}[about_us][value]">{{ $item ? $item['about_us']->translate($locale)->value : null }}</textarea>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="lz-field-label">{{ __('field.privacy_policy') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_privacy_policy') }}</small>
                                        <input type="hidden" name="{{ $locale }}[privacy_policy][key]"
                                            value="{{ App\Enums\PageEnum::PrivacyPolicy->value }}">
                                        <textarea class="summernote" name="{{ $locale }}[privacy_policy][value]">{{ $item ? $item['privacy_policy']->translate($locale)->value : null }}</textarea>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-md-12">
                                        <label class="lz-field-label">{{ __('field.terms_conditions') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_terms_conditions') }}</small>
                                        <input type="hidden" name="{{ $locale }}[terms_conditions][key]"
                                            value="{{ App\Enums\PageEnum::TermsConditions->value }}">
                                        <textarea class="summernote" name="{{ $locale }}[terms_conditions][value]">{{ $item ? $item['terms_conditions']->translate($locale)->value : null }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
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
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/lang/summernote-ar-AR.min.js"
        integrity="sha512-uJrAbZZW6Fc2rWFW9bFNkaZdBfNV5b3sS6WeUZ2kn9UCp5MKLBSU10D75O0s6AHYQwtdSckrKzSCBsUVkm4PUQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        $('.summernote').summernote({
            lang: 'ar-AR',
            placeholder: 'أدخل تنسيق',
            tabsize: 2,
            height: 200
        });
    </script>

    @include('Admin.Components.submit-form-ajax')
@endsection
