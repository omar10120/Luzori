@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.phone')}}</label>
                                        <input type="number" class="form-control" name="phone" value="{{ $item->phone }}"
                                            placeholder="{{__('field.phone')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.email')}}</label>
                                        <input type="text" class="form-control" name="email" value="{{ $item->email }}"
                                            placeholder="{{__('field.email')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.facebook')}}</label>
                                        <input type="text" class="form-control" name="facebook" value="{{ $item->facebook }}"
                                            placeholder="{{__('field.facebook')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.instagram')}}</label>
                                        <input type="text" class="form-control" name="instagram" value="{{ $item->instagram }}"
                                            placeholder="{{__('field.instagram')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.twitter')}}</label>
                                        <input type="text" class="form-control" name="twitter" value="{{ $item->twitter }}"
                                            placeholder="{{__('field.twitter')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.whatsapp')}}</label>
                                        <input type="text" class="form-control" name="whatsapp" value="{{ $item->whatsapp }}"
                                            placeholder="{{__('field.whatsapp')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.youtube')}}</label>
                                        <input type="text" class="form-control" name="youtube" value="{{ $item->youtube }}"
                                            placeholder="{{__('field.youtube')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.linkedin')}}</label>
                                        <input type="text" class="form-control" name="linkedin" value="{{ $item->linkedin }}"
                                            placeholder="{{__('field.linkedin')}}" />
                                    </div>
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
    @include('Admin.Components.submit-form-ajax')
@endsection
