@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
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
                        @include('CenterUser.Components.languages-tabs')
                        
                        <div class="tab-content">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                    aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label for="name" class="form-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                                                <small class="text-muted">{{__('general.enter_the_name_of_the_service')}}</small>
                                                <input type="text" id="name_{{ $locale }}" class="form-control"
                                                    name="{{ $locale }}[name]"
                                                    placeholder="{{ __('field.name') }}"
                                                    value="{{ $item ? $item->translate($locale)->name : '' }}" />
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label for="description" class="form-label">{{ __('field.description') }} <span class="text-danger">*</span></label>
                                                <small class="text-muted">{{__('general.enter_the_description_of_the_service')}}</small>
                                                <textarea id="description_{{ $locale }}" class="form-control"
                                                    name="{{ $locale }}[description]"
                                                    placeholder="{{ __('field.description') }}">{{ $item ? $item->translate($locale)->description : '' }}</textarea>
                                                    
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="rooms_no" class="form-label">{{ __('field.rooms_no') }} <span class="text-danger">*</span> </label>
                                    <small class="text-muted">{{__('general.enter_the_number_of_rooms_of_the_service')}}</small>
                                    <input type="number" id="rooms_no" class="form-control" name="rooms_no"
                                        placeholder="{{ __('field.rooms_no') }}"
                                        value="{{ $item ? $item->rooms_no : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="free_book" class="form-label">{{ __('field.free_book') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_number_of_free_books_of_the_service')}}</small>
                                    <input type="number" id="free_book" class="form-control" name="free_book"
                                        placeholder="{{ __('field.free_book') }}"
                                        value="{{ $item ? $item->free_book : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="price" class="form-label">{{ __('field.price') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_price_of_the_service')}}</small>
                                    <input type="number" id="price" class="form-control" name="price"
                                        placeholder="{{ __('field.price') }}"
                                        value="{{ $item ? $item->price : null }}" />
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="mb-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_top" name="is_top"
                                            @checked($item?->is_top) />
                                        <label class="form-check-label" for="is_top">{{ __('field.is_top') }} <span class="text-danger">*</span></label>
                                        <small class="text-muted">{{__('general.select_if_the_service_is_top')}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="mb-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="has_commission"
                                            name="has_commission" @checked($item?->has_commission) />
                                        <label class="form-check-label"
                                            for="has_commission">{{ __('field.commission') }} <span class="text-danger">*</span></label>
                                        <small class="text-muted">{{__('general.select_if_the_service_has_commission')}}</small>
                                    
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    @include('CenterUser.Components.image', [
                                        'item' => $item,
                                        'name' => 'image',
                                        'model' => 'service',
                                    ])
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
    @include('CenterUser.Components.image-js')
    @include('CenterUser.Components.submit-form-ajax')
    @include('CenterUser.Components.translation-js')
    <script>
        $(document).ready(function() {
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
