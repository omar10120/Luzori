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
                                    <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                        aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.about_us') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[about_us][key]"
                                                        value="{{ App\Enums\PageEnum::AboutUs->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[about_us][value]">{{ $item['about_us']?->translate($locale)?->value }}</textarea>                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.privacy_policy') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[privacy_policy][key]"
                                                        value="{{ App\Enums\PageEnum::PrivacyPolicy->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[privacy_policy][value]">{{ $item['privacy_policy']?->translate($locale)?->value }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.terms_conditions') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[terms_conditions][key]"
                                                        value="{{ App\Enums\PageEnum::TermsConditions->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[terms_conditions][value]">{{ $item['terms_conditions']?->translate($locale)?->value }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <h4 class="mb-3">{{ __('field.invoice_info') }}</h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.invoice_info') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[invoice_info][key]"
                                                        value="{{ App\Enums\PageEnum::InvoiceInfo->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[invoice_info][value]">{{ $item['invoice_info']?->translate($locale)?->value }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <h4 class="mb-3">{{ __('field.app_legal_sections') }}</h4>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.privacy_policy_app') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[privacy_policy_app][key]"
                                                        value="{{ App\Enums\PageEnum::PrivacyPolicyApp->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[privacy_policy_app][value]">{{ $item['privacy_policy_app']?->translate($locale)?->value }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.terms_of_use_app') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[terms_of_use_app][key]"
                                                        value="{{ App\Enums\PageEnum::TermsOfUseApp->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[terms_of_use_app][value]">{{ $item['terms_of_use_app']?->translate($locale)?->value }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.terms_of_service_app') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[terms_of_service_app][key]"
                                                        value="{{ App\Enums\PageEnum::TermsOfServiceApp->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[terms_of_service_app][value]">{{ $item['terms_of_service_app']?->translate($locale)?->value }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.about_us_app') }}</label>
                                                    <input type="hidden" name="{{ $locale }}[about_us_app][key]"
                                                        value="{{ App\Enums\PageEnum::AboutUsApp->value }}">
                                                    <textarea class="summernote" name="{{ $locale }}[about_us_app][value]">{{ $item['about_us_app']?->translate($locale)?->value }}</textarea>
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
