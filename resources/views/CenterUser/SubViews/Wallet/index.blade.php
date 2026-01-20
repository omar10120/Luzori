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
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="amount" class="form-label">{{ __('field.amount') }} <span class="text-danger">*</span> </label>
                                    <small class="text-muted">{{__('general.enter_the_amount_of_the_wallet')}}</small>
                                    <input type="number" id="amount" class="form-control" name="amount"
                                        placeholder="{{ __('field.amount') }}"
                                        value="{{ $item ? $item->amount : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="invoiced_amount" class="form-label">{{ __('field.invoiced_amount') }}
                                    <small class="text-muted">{{__('general.enter_the_invoiced_amount_of_the_wallet')}}</small>
                                    </label>
                                    <input type="number" id="invoiced_amount" class="form-control" name="invoiced_amount"
                                        placeholder="{{ __('field.invoiced_amount') }}"
                                        value="{{ $item ? $item->invoiced_amount : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="start_at" class="form-label">{{ __('field.start_at') }} </label>
                                    <small class="text-muted">{{__('general.select_the_start_date_of_the_wallet')}}</small>
                                    <input type="date" id="start_at" class="form-control" name="start_at"
                                        placeholder="{{ __('field.start_at') }}"
                                        value="{{ $item ? $item->start_at : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="end_at" class="form-label">{{ __('field.end_at') }} </label>
                                    <small class="text-muted">{{__('general.select_the_end_date_of_the_wallet')}}</small>
                                    <input type="date" id="end_at" class="form-control" name="end_at"
                                        placeholder="{{ __('field.end_at') }}"
                                        value="{{ $item ? $item->end_at : null }}" />
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
    @include('CenterUser.Components.submit-form-ajax')
@endsection
