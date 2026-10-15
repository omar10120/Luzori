@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
    @include('CenterUser.Components.settings-form-styles')
@endsection

@section('content')
    <div class="luzori-page lz-settings-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-building-bank"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
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

                    <div class="lz-section-label">
                        <i class="ti ti-building-bank"></i>
                        <span>{{ __('locale.center_info') }}</span>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="lz-field-label" for="bank_name">{{ __('field.bank_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name"
                                placeholder="{{ __('field.bank_name') }}" value="{{ $center->bank_name }}" required />
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="lz-field-label" for="iban">{{ __('field.iban') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="iban" name="iban"
                                placeholder="{{ __('field.iban') }}" value="{{ $center->iban }}" required />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="lz-field-label" for="BankAccountHolderName">{{ __('field.BankAccountHolderName') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="BankAccountHolderName" name="BankAccountHolderName"
                                placeholder="{{ __('field.BankAccountHolderName') }}" value="{{ $center->BankAccountHolderName }}" required />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="lz-field-label" for="BusinessName">{{ __('field.BusinessName') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="BusinessName" name="BusinessName"
                                placeholder="{{ __('field.BusinessName') }}" value="{{ $center->BusinessName }}" required />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="lz-field-label" for="BankId">{{ __('field.BankId') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="BankId" name="BankId" required>
                                <option value="">{{ __('general.choose') }}</option>
                                @if ($center->BankId)
                                    <option value="{{ $center->BankId }}" selected>{{ $center->bank_name ?: $center->BankId }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-12 mb-1">
                            <label class="lz-field-label" for="BankAccount">{{ __('field.BankAccount') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="BankAccount" name="BankAccount"
                                placeholder="{{ __('field.BankAccount') }}" value="{{ $center->BankAccount }}" required />
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
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
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')
    <script>
        $(document).ready(function() {
            const selectedBankId = @json($center->BankId);
            const $bankSelect = $('#BankId');

            $bankSelect.select2({
                placeholder: "{{ __('general.choose') }}",
                allowClear: true,
                width: '100%'
            });

            $.get('{{ route('center_user.get-banks.index') }}', function(banks) {
                const currentValue = selectedBankId ? String(selectedBankId) : '';

                $bankSelect.empty().append(
                    $('<option>', { value: '', text: "{{ __('general.choose') }}" })
                );

                banks.forEach(function(bank) {
                    $bankSelect.append(
                        $('<option>', {
                            value: bank.Value,
                            text: bank.Text,
                            selected: String(bank.Value) === currentValue
                        })
                    );
                });

                if (currentValue) {
                    $bankSelect.val(currentValue).trigger('change');
                }
            }).fail(function(xhr) {
                const message = xhr.responseJSON?.message || "{{ __('admin.an_error_occurred') }}";
                const $alert = $('#alertError');
                const $list = $('#listError');

                $list.empty().append($('<li>').text(message));
                $alert.show();
            });

            $bankSelect.on('change', function() {
                const selectedText = $(this).find('option:selected').text();
                if ($(this).val()) {
                    $('#bank_name').val(selectedText);
                }
            });
        });
    </script>
@endsection
