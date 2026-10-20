@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="luzori-page container-fluid px-3 px-md-4">
        @include('Admin.Components.breadcrumbs')

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-header">
                    <h2>{{ $title }}</h2>
                </div>
                <div class="card-body">
                    @include('Admin.Components.languages-tabs')
                    <div class="tab-content mt-3">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                <div class="mb-4">
                                    <label class="form-label">{{ __('field.about_us') }}</label>
                                    <input type="hidden" name="{{ $locale }}[about_us][key]"
                                        value="{{ App\Enums\PageEnum::AboutUs->value }}">
                                    <textarea class="summernote" name="{{ $locale }}[about_us][value]">{{ $item['about_us']?->translate($locale)?->value }}</textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">{{ __('field.privacy_policy') }}</label>
                                    <input type="hidden" name="{{ $locale }}[privacy_policy][key]"
                                        value="{{ App\Enums\PageEnum::PrivacyPolicy->value }}">
                                    <textarea class="summernote" name="{{ $locale }}[privacy_policy][value]">{{ $item['privacy_policy']?->translate($locale)?->value }}</textarea>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label">{{ __('field.terms_conditions') }}</label>
                                    <input type="hidden" name="{{ $locale }}[terms_conditions][key]"
                                        value="{{ App\Enums\PageEnum::TermsConditions->value }}">
                                    <textarea class="summernote" name="{{ $locale }}[terms_conditions][value]">{{ $item['terms_conditions']?->translate($locale)?->value }}</textarea>
                                </div>

                                <hr class="lz-form-divider">
                                <h5 class="lz-form-section-title">{{ __('field.invoice_info') }}</h5>
                                <div class="mb-4">
                                    <label class="form-label">{{ __('field.invoice_info') }}</label>
                                    <input type="hidden" name="{{ $locale }}[invoice_info][key]"
                                        value="{{ App\Enums\PageEnum::InvoiceInfo->value }}">
                                    <textarea class="summernote" name="{{ $locale }}[invoice_info][value]">{{ $item['invoice_info']?->translate($locale)?->value }}</textarea>
                                </div>

                                <hr class="lz-form-divider">
                                <h5 class="lz-form-section-title">{{ __('field.app_legal_sections') }}</h5>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.privacy_policy_app') }}</label>
                                    <input type="hidden" name="{{ $locale }}[privacy_policy_app][key]"
                                        value="{{ App\Enums\PageEnum::PrivacyPolicyApp->value }}">
                                    <textarea rows="5" class="form-control"
                                        name="{{ $locale }}[privacy_policy_app][value]">{{ $item['privacy_policy_app']?->translate($locale)?->value }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.terms_of_use_app') }}</label>
                                    <input type="hidden" name="{{ $locale }}[terms_of_use_app][key]"
                                        value="{{ App\Enums\PageEnum::TermsOfUseApp->value }}">
                                    <textarea rows="5" class="form-control"
                                        name="{{ $locale }}[terms_of_use_app][value]">{{ $item['terms_of_use_app']?->translate($locale)?->value }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.terms_of_service_app') }}</label>
                                    <input type="hidden" name="{{ $locale }}[terms_of_service_app][key]"
                                        value="{{ App\Enums\PageEnum::TermsOfServiceApp->value }}">
                                    <textarea rows="5" class="form-control"
                                        name="{{ $locale }}[terms_of_service_app][value]">{{ $item['terms_of_service_app']?->translate($locale)?->value }}</textarea>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.about_us_app') }}</label>
                                    <input type="hidden" name="{{ $locale }}[about_us_app][key]"
                                        value="{{ App\Enums\PageEnum::AboutUsApp->value }}">
                                    <textarea rows="5" class="form-control"
                                        name="{{ $locale }}[about_us_app][value]">{{ $item['about_us_app']?->translate($locale)?->value }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary submitFrom">
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
