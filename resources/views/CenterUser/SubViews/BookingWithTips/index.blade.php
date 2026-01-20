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
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="mb-1">
                                    <label for="branch_id" class="form-label">{{ __('field.branch') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_branch_from_the_list')}}</small>
                                    <select class="form-control" name="branch_id" id="branch_id">
                                        <option value="">{{ __('general.choose') . ' ' . __('field.branch') }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="mb-1">
                                    <label for="worker_id" class="form-label">{{ __('field.worker') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_worker_from_the_list')}}</small>
                                    <select class="form-control" name="worker_id" id="worker_id">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="mb-1">
                                    <label for="date_time" class="form-label">{{ __('field.booking_date_time') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_date_and_time')}}</small>
                                    <input type="datetime-local" id="date_time" class="form-control" name="date_time" />
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="mb-1">
                                    <label for="tip" class="form-label">{{ __('field.tip') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_tip_amount')}}</small>
                                        ({{ __('field.between_0_200') }})</label>
                                    <input type="number" class="form-control" name="tip" id="tip">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="payment_type" class="form-label">{{ __('field.payment_method') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_payment_method_from_the_list')}}</small>
                                    <select name="payment_type" id="payment_type" class="form-control">
                                        <option value="">{{ __('field.Choose Payment Method') }}</option>
                                        @foreach($paymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                        @endforeach
                                    </select>
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

    <script>
        $('#branch_id').on('change', function() {
            var workers = get_worker($(this).val());
            let reviewHtml = ``
            $.each(workers, function(index, item) {
                reviewHtml += `<option value="${item.id}">${item.name}</option>`;
            });
            $('#worker_id').html(reviewHtml);
        });

        function get_worker(branch_id) {
            var workers = [];
            $.ajax({
                url: "{{ route('center_user.booking_with_tips.get-workers') }}",
                method: 'GET',
                async: false,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    branch_id: branch_id,
                },
                success: function(response) {
                    workers = response;
                }
            });
            return workers;
        }
    </script>
@endsection
