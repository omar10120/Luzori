@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')

    <style>
        .note-editable {
            background: #FFF;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

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
                            @include('Admin.Components.languages-tabs')
                            <div class="tab-content">
                                @foreach (Config::get('translatable.locales') as $locale)
                                    <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}"
                                        aria-labelledby="{{ $locale }}-tab" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.about_us') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[about_us][key]"
                                                        value="{{ App\Enums\PageEnum::AboutUs->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[about_us][value]">{{ $item ? $item['about_us']->translate($locale)->value : null }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.privacy_policy') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[privacy_policy][key]"
                                                        value="{{ App\Enums\PageEnum::PrivacyPolicy->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[privacy_policy][value]">{{ $item ? $item['privacy_policy']->translate($locale)->value : null }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.terms_conditions') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[terms_conditions][key]"
                                                        value="{{ App\Enums\PageEnum::TermsConditions->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[terms_conditions][value]">{{ $item ? $item['terms_conditions']->translate($locale)->value : null }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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
