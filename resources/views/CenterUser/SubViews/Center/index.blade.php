@extends('layouts/layoutMaster')

@section('title', $title)

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
                        <div class="container">
                            
                            @if (session('warning'))
                                <div class="alert alert-warning alert-dismissible" role="alert">
                                    {{ session('warning') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="alert alert-danger alert-dismissible" id="alertError" style="display: none;" role="alert">
                                <ul id="listError" class="mb-0"></ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <div class="alert alert-success alert-dismissible" id="alertSuccess" style="display: none;" role="alert">
                                <span id="successMessage"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="iban">{{ __('field.bank_name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name"
                                            placeholder="{{ __('field.bank_name') }}" value="{{ $center->bank_name }}" required />
                                    </div>
                                </div>
                                
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="iban">{{ __('field.iban') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="iban" name="iban"
                                            placeholder="{{ __('field.iban') }}" value="{{ $center->iban }}" required />
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="BankAccountHolderName">{{ __('field.BankAccountHolderName') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="BankAccountHolderName" name="BankAccountHolderName"
                                            placeholder="{{ __('field.BankAccountHolderName') }}" value="{{ $center->BankAccountHolderName }}" required />
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="BusinessName">{{ __('field.BusinessName') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="BusinessName" name="BusinessName"
                                            placeholder="{{ __('field.BusinessName') }}" value="{{ $center->BusinessName }}" required />
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="BankAccount">{{ __('field.BankAccount') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="BankAccount" name="BankAccount"
                                            placeholder="{{ __('field.BankAccount') }}" value="{{ $center->BankAccount }}" required />
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

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')
@endsection
