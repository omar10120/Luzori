@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="luzori-page lz-package-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-packages"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.packages') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.packages') }}
                    @endif
                </h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <ul class="nav nav-tabs lz-lang-tabs" role="tablist">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $loop->first ? 'active' : null }}"
                                    id="{{ $locale }}-tab-add" data-bs-toggle="tab"
                                    href="#{{ $locale }}-add" aria-controls="{{ $locale }}-add"
                                    role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ Str::upper($locale) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content pt-3">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="name_{{ $locale }}" class="lz-field-label">
                                            {{ __('field.name') }} <span class="text-danger">*</span>
                                        </label>
                                        <small class="lz-field-help">{{ __('general.enter_the_name_of_the_package') }}</small>
                                        <input type="text" id="name_{{ $locale }}" class="form-control"
                                            name="{{ $locale }}[name]" placeholder="{{ __('field.name') }}"
                                            value="{{ $item ? $item->translate($locale)->name : '' }}" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="lz-field-label">
                                {{ __('field.price') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_price_of_the_package') }}</small>
                            <input type="number" step="0.01" id="price" class="form-control" name="price"
                                placeholder="{{ __('field.price') }}" value="{{ $item ? $item->price : '' }}" required />
                            @if ($item)
                                <div class="mt-2">
                                    <span class="lz-percent-badge" style="display:inline-flex;padding:0.28rem 0.7rem;border-radius:999px;background:#f3ebe0;color:#8a6a3d;font-size:0.72rem;font-weight:700;">
                                        {{ __('field.total_services_price') }}: {{ number_format($item->total_value, 2) }} {{ get_currency() }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="paid_services" class="lz-field-label">
                                {{ __('field.paid_services') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_paid_services_from_the_list') }}</small>
                            <select class="select2 form-select" name="paid_services[]" id="paid_services" multiple required>
                                @foreach ($services as $service)
                                    <option
                                        {{ $item ? (in_array($service->id, $item->packageServicePaid()->pluck('service_id')->toArray()) ? 'selected' : null) : null }}
                                        value="{{ $service->id }}">{{ $service->name }} ({{ number_format($service->price, 2) }} {{ get_currency() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="free_services" class="lz-field-label">{{ __('field.free_services') }}</label>
                            <small class="lz-field-help">{{ __('general.select_a_free_services_from_the_list') }}</small>
                            <select class="select2 form-select" name="free_services[]" id="free_services" multiple>
                                @foreach ($services as $service)
                                    <option
                                        {{ $item ? (in_array($service->id, $item->packageServiceFree()->pluck('service_id')->toArray()) ? 'selected' : null) : null }}
                                        value="{{ $service->id }}">{{ $service->name }} ({{ number_format($service->price, 2) }} {{ get_currency() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.packages.index') }}" class="btn btn-luzori-beige">
                        {{ __('general.cancel') }}
                    </a>
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
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

    @include('CenterUser.Components.submit-form-ajax')
    @include('CenterUser.Components.translation-js')
    <script>
        $(document).ready(function () {
            $('#name_en').on('input', function () {
                debouncedTranslate($(this).val(), 'en', 'ar', 'name_ar');
            });
            $('#name_ar').on('input', function () {
                debouncedTranslate($(this).val(), 'ar', 'en', 'name_en');
            });
        });
    </script>
@endsection