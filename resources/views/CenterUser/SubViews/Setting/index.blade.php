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
                    <i class="ti ti-settings"></i>
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
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.website_name') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_name_of_the_website') }}</small>
                                        <input type="text" class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteName->value }}]"
                                            placeholder="{{ __('field.website_name') }}"
                                            value="{{ $item['item']['WebsiteName']->translate($locale)->value }}" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.website_title') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_title_of_the_website') }}</small>
                                        <input type="text" class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteTitle->value }}]"
                                            placeholder="{{ __('field.website_title') }}"
                                            value="{{ $item['item']['WebsiteTitle']->translate($locale)->value }}" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.auther') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_name_of_the_author') }}</small>
                                        <input type="text" class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::Auther->value }}]"
                                            placeholder="{{ __('field.auther') }}"
                                            value="{{ $item['item']['Auther']->translate($locale)->value }}" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.website_description') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_description_of_the_website') }}</small>
                                        <textarea class="form-control" name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteDescription->value }}]"
                                            placeholder="{{ __('field.website_description') }}" cols="30" rows="6">{{ $item['item']['WebsiteDescription']->translate($locale)->value }}</textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.address') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_address_of_the_website') }}</small>
                                        <textarea class="form-control" name="{{ $locale }}[{{ App\Enums\PageEnum::Address->value }}]" placeholder="{{ __('field.address') }}"
                                            cols="30" rows="6">{{ $item['item']['Address']->translate($locale)->value }}</textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.footer_text') }} <span class="text-danger">*</span></label>
                                        <small class="lz-field-help">{{ __('general.enter_the_footer_text_of_the_website') }}</small>
                                        <textarea class="form-control" name="{{ $locale }}[{{ App\Enums\PageEnum::FooterText->value }}]" placeholder="{{ __('field.footer_text') }}"
                                            cols="30" rows="6">{{ $item['item']['FooterText']->translate($locale)->value }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="lz-section-label mt-2">
                        <i class="ti ti-adjustments"></i>
                        <span>{{ __('locale.settings') }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="language" class="lz-field-label">{{ __('admin.System Language') }}</label>
                            <select class="form-control" name="language" id="language">
                                <option @selected($item['language']->value == 'ar') value="ar">Ar</option>
                                <option @selected($item['language']->value == 'en') value="en">En</option>
                                <option @selected($item['language']->value == 'fr') value="fr">Fr</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tips" class="lz-field-label">{{ __('admin.Tips (%)') }}</label>
                            <input type="text" class="form-control" name="tips" id="tips"
                                value="{{ $item['tips']->value }}" placeholder="{{ __('admin.Tips (%)') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="lz-field-label">Choose New Logo</label>
                            <input type="file" class="form-control" id="image" name="image" />
                            <img id="show_image" src="{{ $item['image'] }}"
                                style="width:160px;height:160px;margin-top:1rem;" alt="setting image" />
                        </div>
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

    @include('Admin.Components.image-js')
    @include('Admin.Components.submit-form-ajax')
@endsection
