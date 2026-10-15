@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="luzori-page lz-discount-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-percentage"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.discounts') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.discounts') }}
                    @endif
                </h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="type" class="lz-field-label">
                                {{ __('field.type') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_type_of_discount') }}</small>
                            <select class="form-select" id="type" name="type">
                                <option {{ $item ? ($item->type == 'fixed' ? 'selected' : null) : null }}
                                    value="fixed">{{ __('field.fixed') }}</option>
                                <option {{ $item ? ($item->type == 'percentage' ? 'selected' : null) : null }}
                                    value="percentage">{{ __('field.percentage') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="using_type" class="lz-field-label">
                                {{ __('field.using_type') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_using_type_of_discount') }}</small>
                            <select class="form-select" id="using_type" name="using_type">
                                <option {{ $item ? ($item->using_type == 'single' ? 'selected' : null) : null }}
                                    value="single">{{ __('field.single') }}</option>
                                <option {{ $item ? ($item->using_type == 'multi' ? 'selected' : null) : null }}
                                    value="multi">{{ __('field.multi') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="benefit_numbers"
                            {{ $item ? ($item->using_type != 'multi' ? 'style=display:none;' : null) : 'style=display:none;' }}>
                            <label for="benefit_numbers_input" class="lz-field-label">
                                {{ __('field.benefit_numbers') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_benefit_numbers_of_the_discount') }}</small>
                            <input type="number" id="benefit_numbers_input" class="form-control" name="benefit_numbers"
                                placeholder="{{ __('field.benefit_numbers') }}"
                                value="{{ $item ? $item->benefit_numbers : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="lz-field-label">
                                {{ __('field.amount') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_amount_of_the_discount') }}</small>
                            <input type="number" id="amount" class="form-control" name="amount"
                                placeholder="{{ __('field.amount') }}"
                                value="{{ $item ? $item->amount : null }}" />
                        </div>
                        @php
                            if ($item) {
                                if (str_contains($item->start_at, '/')) {
                                    $start_at = DateTime::createFromFormat('d/m/Y', $item->start_at);
                                    $item->start_at = $start_at->format('Y-m-d');
                                }
                                if (str_contains($item->end_at, '/')) {
                                    $end_at = DateTime::createFromFormat('d/m/Y', $item->end_at);
                                    $item->end_at = $end_at->format('Y-m-d');
                                }
                            }
                        @endphp
                        <div class="col-md-6 mb-3">
                            <label for="start_at" class="lz-field-label">
                                {{ __('field.start_at') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_start_date_of_the_discount') }}</small>
                            <input type="date" id="start_at" class="form-control" name="start_at"
                                value="{{ $item ? $item->start_at : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_at" class="lz-field-label">
                                {{ __('field.end_at') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_end_date_of_the_discount') }}</small>
                            <input type="date" id="end_at" class="form-control" name="end_at"
                                value="{{ $item ? $item->end_at : null }}" />
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.discounts.index') }}" class="btn btn-luzori-beige">
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
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')

    <script>
        $('#using_type').change(function () {
            if ($(this).val() == 'multi') {
                $('#benefit_numbers').show();
            } else {
                $('#benefit_numbers').hide();
            }
        });
    </script>
@endsection
