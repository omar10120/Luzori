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
                                <div class="col-md-2">
                                    @include('Admin.Components.country_code', ['item' => $item])
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.phone')}} <small class="text-muted">{{__('general.enter_the_phone_number')}}</small> <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="phone" value="{{ $item->phone }}"
                                            placeholder="5XXXXXXX" 
                                            required
                                            maxlength="8"
                                            pattern="5[0-9]{7}"
                                            title="Phone number must start with 5 and be exactly 8 digits" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.email')}} <small class="text-muted">{{__('general.enter_the_email_address')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="email" value="{{ $item->email }}"
                                            placeholder="{{__('field.email')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.facebook')}} <small class="text-muted">{{__('general.enter_the_facebook_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="facebook" value="{{ $item->facebook }}"
                                            placeholder="{{__('field.facebook')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.instagram')}} <small class="text-muted">{{__('general.enter_the_instagram_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="instagram" value="{{ $item->instagram }}"
                                            placeholder="{{__('field.instagram')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.twitter')}} <small class="text-muted">{{__('general.enter_the_twitter_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="twitter" value="{{ $item->twitter }}"
                                            placeholder="{{__('field.twitter')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.whatsapp')}} <small class="text-muted">{{__('general.enter_the_whatsapp_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="whatsapp" value="{{ $item->whatsapp }}"
                                            placeholder="{{__('field.whatsapp')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.youtube')}} <small class="text-muted">{{__('general.enter_the_youtube_url')}}</small> <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="youtube" value="{{ $item->youtube }}"
                                            placeholder="{{__('field.youtube')}}" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <label class="form-label">{{__('field.linkedin')}} <small class="text-muted">{{__('general.enter_the_linkedin_url')}}</small> <span class="text-danger">*</span></label>
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
