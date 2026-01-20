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
                                            <div class="col-md-12 mb-4">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.website_name') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_name_of_the_website')}}</small>
                                                    <input type="text" class="form-control"
                                                        name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteName->value }}]"
                                                        placeholder="{{ __('field.website_name') }}"
                                                        value="{{ $item['item']['WebsiteName']->translate($locale)->value }}" />
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-4">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.website_title') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_title_of_the_website')}}</small>
                                                    <input type="text" class="form-control"
                                                        name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteTitle->value }}]"
                                                        placeholder="{{ __('field.website_title') }}"
                                                        value="{{ $item['item']['WebsiteTitle']->translate($locale)->value }}" />
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-4">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.auther') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_name_of_the_author')}}</small>
                                                    <input type="text" class="form-control"
                                                        name="{{ $locale }}[{{ App\Enums\PageEnum::Auther->value }}]"
                                                        placeholder="{{ __('field.auther') }}"
                                                        value="{{ $item['item']['Auther']->translate($locale)->value }}" />
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-4">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.website_description') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_description_of_the_website')}}</small>
                                                    <textarea class="form-control" name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteDescription->value }}]"
                                                        placeholder="{{ __('field.website_description') }}" cols="30" rows="10">{{ $item['item']['WebsiteDescription']->translate($locale)->value }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-4">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.address') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_address_of_the_website')}}</small>
                                                    <textarea class="form-control" name="{{ $locale }}[{{ App\Enums\PageEnum::Address->value }}]" placeholder="{{ __('field.address') }}"
                                                        cols="30" rows="10">{{ $item['item']['Address']->translate($locale)->value }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-4">
                                                <div class="mb-1">
                                                    <label class="form-label">{{ __('field.footer_text') }} <span class="text-danger">*</span></label>
                                                    <small class="text-muted">{{__('general.enter_the_footer_text_of_the_website')}}</small>
                                                    <textarea class="form-control" name="{{ $locale }}[{{ App\Enums\PageEnum::FooterText->value }}]" placeholder="{{ __('field.footer_text') }}"
                                                        cols="30" rows="10">{{ $item['item']['FooterText']->translate($locale)->value }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label for="language" class="form-label">{{ __('admin.System Language') }}</label>
                                        <select class="form-control" name="language" id="language">
                                            <option @selected($item['language']->value == 'ar') value="ar">Ar</option>
                                            <option @selected($item['language']->value == 'en') value="en">En</option>
                                            <option @selected($item['language']->value == 'fr') value="fr">Fr</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label for="tips" class="form-label">{{ __('admin.Tips (%)') }}</label>
                                        <input type="text" class="form-control" name="tips" id="tips"
                                            value="{{ $item['tips']->value }}" placeholder="{{ __('admin.Tips (%)') }}">
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label">{{ __('field.invoice_info') }}</label>
                                        <textarea class="summernote" name="invoice_info">{{ $item['invoice_info']->value }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <div class="mb-1">
                                            <label class="form-label">Choose New Logo</label>
                                            <input type="file" class="form-control" id="image" name="image" />
                                        </div>
                                        <img id="show_image" src="{{ $item['image'] }}"
                                            style="width:200px;height:200px;margin:20px;" alt="setting image" />
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
