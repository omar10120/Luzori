@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="luzori-page lz-wallet-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-wallet"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.coupons') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.coupons') }}
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
                            <label for="serial_number" class="lz-field-label">{{ __('field.serial_number') }}</label>
                            <small class="lz-field-help">{{ __('general.enter_the_serial_number_of_the_wallet') }}</small>
                            <input type="text" id="serial_number" class="form-control" name="serial_number"
                                placeholder="{{ __('field.serial_number') }}"
                                value="{{ $item ? $item->serial_number : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="lz-field-label">
                                {{ __('field.amount') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_amount_of_the_wallet') }}</small>
                            <input type="number" id="amount" class="form-control" name="amount"
                                placeholder="{{ __('field.amount') }}"
                                value="{{ $item ? $item->amount : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="invoiced_amount" class="lz-field-label">{{ __('field.invoiced_amount') }}</label>
                            <small class="lz-field-help">{{ __('general.enter_the_invoiced_amount_of_the_wallet') }}</small>
                            <input type="number" id="invoiced_amount" class="form-control" name="invoiced_amount"
                                placeholder="{{ __('field.invoiced_amount') }}"
                                value="{{ $item ? $item->invoiced_amount : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="start_at" class="lz-field-label">{{ __('field.start_at') }}</label>
                            <small class="lz-field-help">{{ __('general.select_the_start_date_of_the_wallet') }}</small>
                            <input type="date" id="start_at" class="form-control" name="start_at"
                                value="{{ $item ? $item->start_at : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_at" class="lz-field-label">{{ __('field.end_at') }}</label>
                            <small class="lz-field-help">{{ __('general.select_the_end_date_of_the_wallet') }}</small>
                            <input type="date" id="end_at" class="form-control" name="end_at"
                                value="{{ $item ? $item->end_at : null }}" />
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.wallets.index') }}" class="btn btn-luzori-beige">
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
@endsection
