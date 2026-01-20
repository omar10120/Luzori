@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
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
                                                <label for="name" class="form-label">{{ __('field.name') }}</label>
                                                <input type="text" id="name_{{ $locale }}" class="form-control"
                                                    name="{{ $locale }}[name]" placeholder="{{ __('field.name') }}"
                                                    value="{{ $item ? $item->translate($locale)->name : '' }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="paid_services" class="form-label">{{ __('field.paid_services') }} </label>
                                    <select class="select2 form-control" name="paid_services[]" id="paid_services" multiple>
                                        @foreach ($services as $service)
                                            <option
                                                {{ $item ? (in_array($service->id, $item->packageServicePaid()->pluck('service_id')->toArray()) ? 'selected' : null) : null }}
                                                value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="free_services" class="form-label">{{ __('field.free_services') }} </label>
                                    <select class="select2 form-control" name="free_services[]" id="free_services" multiple>
                                        @foreach ($services as $service)
                                            <option
                                                {{ $item ? (in_array($service->id, $item->packageServiceFree()->pluck('service_id')->toArray()) ? 'selected' : null) : null }}
                                                value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
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
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

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
        });
    </script>
@endsection
