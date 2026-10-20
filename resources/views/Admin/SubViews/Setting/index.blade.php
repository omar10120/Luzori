@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="luzori-page container-fluid px-3 px-md-4">
        @include('Admin.Components.breadcrumbs')

        <form class="pt-0" id="frmSubmit" enctype="multipart/form-data">
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
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('field.website_name') }}</label>
                                        <input type="text" class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteName->value }}]"
                                            placeholder="{{ __('field.website_name') }}"
                                            value="{{ $item['item']['WebsiteName']->translate($locale)->value }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('field.website_title') }}</label>
                                        <input type="text" class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteTitle->value }}]"
                                            placeholder="{{ __('field.website_title') }}"
                                            value="{{ $item['item']['WebsiteTitle']->translate($locale)->value }}" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('field.auther') }}</label>
                                        <input type="text" class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::Auther->value }}]"
                                            placeholder="{{ __('field.auther') }}"
                                            value="{{ $item['item']['Auther']->translate($locale)->value }}" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('field.website_description') }}</label>
                                        <textarea class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::WebsiteDescription->value }}]"
                                            placeholder="{{ __('field.website_description') }}"
                                            rows="5">{{ $item['item']['WebsiteDescription']->translate($locale)->value }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('field.address') }}</label>
                                        <textarea class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::Address->value }}]"
                                            placeholder="{{ __('field.address') }}"
                                            rows="4">{{ $item['item']['Address']->translate($locale)->value }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('field.footer_text') }}</label>
                                        <textarea class="form-control"
                                            name="{{ $locale }}[{{ App\Enums\PageEnum::FooterText->value }}]"
                                            placeholder="{{ __('field.footer_text') }}"
                                            rows="4">{{ $item['item']['FooterText']->translate($locale)->value }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="lz-form-divider my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="language" class="form-label">{{ __('field.language') }}</label>
                            <select class="form-select" name="language" id="language">
                                <option @selected($item['language']->value == 'ar') value="ar">AR</option>
                                <option @selected($item['language']->value == 'en') value="en">EN</option>
                                <option @selected($item['language']->value == 'fr') value="fr">FR</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tips" class="form-label">{{ __('field.tips') }} (%)</label>
                            <input type="text" class="form-control" name="tips" id="tips"
                                value="{{ $item['tips']->value }}" placeholder="{{ __('field.tips') }} (%)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="image">{{ __('field.logo') }}</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" />
                            <div class="lz-setting-logo-preview mt-3">
                                <img id="show_image" src="{{ $item['image'] }}" alt="{{ __('field.logo') }}" />
                            </div>
                        </div>
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
    @include('Admin.Components.image-js')
    @include('Admin.Components.submit-form-ajax')
@endsection
