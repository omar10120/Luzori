@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss']);
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
                        <div class="row" id="users">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">{{__('field.users')}}</label>
                                    <select class="select2 form-control" name="users[]" multiple>
                                        <option value="all">{{__('field.all_users')}}</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                @include('CenterUser.Components.languages-tabs')
                                
                                <div class="tab-content">
                                    @foreach (Config::get('translatable.locales') as $locale)
                                        <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}"
                                            aria-labelledby="{{ $locale }}-tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-1">
                                                        <label class="form-label">{{__('field.address')}}</label>
                                                        <input type="text" class="form-control"
                                                            name="{{ $locale }}[title]" placeholder="{{__('field.address')}}" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-1">
                                                        <label class="form-label">{{__('field.text')}}</label>
                                                        <textarea name="{{ $locale }}[text]" class="form-control" cols="30" rows="10" placeholder="{{__('field.text')}}"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js']);
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-selects.js', 'resources/assets/js/app-ecommerce-product-add.js']);
    @include('CenterUser.Components.submit-form-ajax')
@endsection
