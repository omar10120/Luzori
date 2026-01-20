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
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.first_name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name"
                                        placeholder="{{__('field.first_name')}}" value="{{ $item ? $item->first_name : null }}" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.last_name')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name"
                                        placeholder="{{__('field.last_name')}}" value="{{ $item ? $item->last_name : null }}" required />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.email')}} <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control dt-email"
                                        placeholder="example@domain.com" value="{{ $item ? $item->email : null }}" required />
                                </div>
                            </div>
                            <div class="col-md-2">
                                @include('Admin.Components.country_code', ['item' => $item])
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.mobile_number')}} <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" maxlength="8" class="form-control"
                                        placeholder="{{__('field.mobile_number')}}" value="{{ $item ? $item->phone : null }}" 
                                        required 
                                        pattern="[0-9]{7,15}"
                                        title="Please enter a valid phone number (7-15 digits)" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                @include('CenterUser.Components.image', [
                                    'item' => $item,
                                    'name' => 'image',
                                    'model' => 'user',
                                ])
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
@endsection
