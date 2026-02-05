@extends('layouts/layoutMaster')

@section('title', $title)

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
                        <div class="container">
                            @include('CenterUser.Components.languages-tabs')
                            
                            <div class="tab-content">
                                @foreach (Config::get('translatable.locales') as $locale)
                                    <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                        aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <div class="mb-1">
                                                    <label class="form-label">{{__('field.name')}}  <span class="text-danger">*</span></label>
                                                    <input type="text" id="name_{{ $locale }}" class="form-control"
                                                        name="{{ $locale }}[name]" placeholder="{{__('field.name')}}"
                                                        value="{{ $item ? $item->translate($locale)->name : '' }}" />
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <div class="mb-1">
                                                    <label class="form-label">{{__('field.description')}}</label>
                                                    <textarea id="description_{{ $locale }}" name="{{ $locale }}[description]" class="form-control" cols="25" 
                                                        rows="5" placeholder="{{__('field.description')}}">{{ $item ? $item->translate($locale)->description : '' }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-12 mb-2">
                                                <div class="mb-1">
                                                    <label class="form-label">{{__('field.keywords')}}</label>
                                                    <input type="text" id="keywords_{{ $locale }}" class="form-control"
                                                        name="{{ $locale }}[keywords]" placeholder="{{__('field.keywords')}}"
                                                        value="{{ $item ? $item->translate($locale)->keywords : '' }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12 mb-2">
                                    @include('CenterUser.Components.category-tree-select', [
                                        'categoriesJson' => $categoriesJson,
                                        'selectedId' => $selectedId,
                                        'selectedName' => $selectedName,
                                        'name' => 'parent_id',
                                        'label' => __('field.parent_category')
                                    ])
                                    <small class="text-muted">{{__('general.select_a_parent_category_if_this_is_a_subcategory')}}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-sm submitFrom">
                            <i class="menu-icon tf-icons ti ti-check"></i>
                            <span>{{ __('general.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
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

            // Listeners for Keywords
            $('#keywords_en').on('input', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'keywords_ar');
            });
            $('#keywords_ar').on('input', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'keywords_en');
            });
        });
    </script>
@endsection
