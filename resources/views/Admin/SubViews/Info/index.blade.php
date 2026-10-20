@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="luzori-page container-fluid px-3 px-md-4">
        @include('Admin.Components.breadcrumbs')

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-header">
                    <h2>{{ $title }}</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.phone') }}</label>
                            <input type="text" class="form-control" name="phone" value="{{ $item->phone }}"
                                placeholder="{{ __('field.phone') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.email') }}</label>
                            <input type="email" class="form-control" name="email" value="{{ $item->email }}"
                                placeholder="{{ __('field.email') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.facebook') }}</label>
                            <input type="url" class="form-control" name="facebook" value="{{ $item->facebook }}"
                                placeholder="{{ __('field.facebook') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.instagram') }}</label>
                            <input type="url" class="form-control" name="instagram" value="{{ $item->instagram }}"
                                placeholder="{{ __('field.instagram') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.twitter') }}</label>
                            <input type="url" class="form-control" name="twitter" value="{{ $item->twitter }}"
                                placeholder="{{ __('field.twitter') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.whatsapp') }}</label>
                            <input type="text" class="form-control" name="whatsapp" value="{{ $item->whatsapp }}"
                                placeholder="{{ __('field.whatsapp') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.youtube') }}</label>
                            <input type="url" class="form-control" name="youtube" value="{{ $item->youtube }}"
                                placeholder="{{ __('field.youtube') }}" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('field.linkedin') }}</label>
                            <input type="url" class="form-control" name="linkedin" value="{{ $item->linkedin }}"
                                placeholder="{{ __('field.linkedin') }}" />
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
    @include('Admin.Components.submit-form-ajax')
@endsection
