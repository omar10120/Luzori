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
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="name" class="form-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_name_of_the_shift')}}</small>
                                    <input type="text" id="name" class="form-control"
                                        name="name" placeholder="{{ __('field.name')}}"
                                        value="{{ $item ? $item->name : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="start_time" class="form-label">{{ __('field.start_time') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_start_time_of_the_shift')}}</small>
                                    <input type="time" id="start_time" class="form-control"
                                        name="start_time" placeholder=""
                                        value="{{ $item ? $item->start_time : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="end_time" class="form-label">{{ __('field.end_time') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_end_time_of_the_shift')}}</small>
                                    <input type="time" id="end_time" class="form-control"
                                        name="end_time" placeholder=""
                                        value="{{ $item ? $item->end_time : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="break_start" class="form-label">{{ __('field.break_start') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_break_start_time_of_the_shift')}}</small>
                                    <input type="time" id="break_start" class="form-control"
                                        name="break_start" placeholder=""
                                        value="{{ $item ? $item->break_start : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="break_end" class="form-label">{{ __('field.break_end') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_break_end_time_of_the_shift')}}</small>
                                    <input type="time" id="break_end" class="form-control"
                                        name="break_end" placeholder=""
                                        value="{{ $item ? $item->break_end : null }}" />
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
