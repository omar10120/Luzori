@extends('layouts/layoutMaster')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="caption">
                <i class="fa fa-plus"></i>{{ __('locale.tips_report') }}
            </div>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('center_user.reports.tips-report') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ __('field.year') }}</label>
                        <select class="form-control select" name="year" required>
                            @if (!empty($years))
                                @foreach ($years as $year)
                                    @php
                                        $selected = '';
                                        if ($year == $selected_year) {
                                            $selected = 'selected=""';
                                        }
                                    @endphp
                                    <option {{ $selected }} value="{{ $year->year }}">{{ $year->year }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>{{ __('field.month') }}</label>
                        <select class="form-control select" name="month" required>
                            @for ($i = 1; $i <= 12; $i++)
                                @php
                                    $selected = '';
                                    if ($i == $selected_month) {
                                        $selected = 'selected=""';
                                    }
                                @endphp
                                <option {{ $selected }} value="{{ $i }}">
                                    {{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('field.branch') }}</label>
                            <select class="form-control" name="branch_id" id="branch_id">
                                @if (get_user_role() == 1 || get_user_role() == 3)
                                    <option value="">{{ __('field.all_branches') }}</option>
                                @endif
                                @if (!empty($branches))
                                    @foreach ($branches as $branch)
                                        @php
                                            $selected = '';
                                            $user_branch_id = auth('center_user')->user()->branch_id;
                                            if ($branch->id == $user_branch_id) {
                                                $selected = 'selected="selected"';
                                            }
                                        @endphp
                                        <option {{ $selected }} value="{{ $branch->id }}">{{ $branch->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('field.worker') }}</label>
                            <select class="form-control" name="worker_id" id="worker_id">
                                <!-- Placeholder option -->
                                <option value="">{{ __('field.select_worker_first') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label><br>
                            <button class="btn btn-success">{{ __('field.view') }}</button>
                            @if (isset($request) && !empty($request->has('year')))
                                <a target="_blank"
                                    href="{{ route('center_user.reports.tips-report') }}?year={{ $request->get('year') }}&month={{ $request->get('month') }}&branch_id={{ $request->get('branch_id') }}&worker_id={{ $request->get('worker_id') }}&is_pdf=true"
                                    class="btn btn-primary">PDF</a>
                                @if ($selected_worker)
                                    <a target="_blank"
                                        href="{{ route('center_user.reports.tips-report') }}?year={{ $request->get('year') }}&month={{ $request->get('month') }}&branch_id={{ $request->get('branch_id') }}&worker_id={{ $request->get('worker_id') }}&is_print=true"
                                        class="btn btn-primary">{{ __('general.print') }}</a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-md-12">
                    {!! $template !!}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Assuming you're using jQuery
            $(document).ready(function() {
                $('.print-row').on('click', function(e) {
                    e.preventDefault();
                    var invoiceUrl = $(this).attr('href');
                    printInvoice(invoiceUrl);
                });

                function printInvoice(url) {
                    var printWindow = window.open(url, '_blank');
                    printWindow.onload = function() {
                        printWindow.print();
                    };
                }
            });
        </script>

        <script>
            $(document).ready(function() {
                $('select[name="branch_id"]').change(function() {
                    var selectedBranch = $(this).val();
                    var workerDropdown = $('select[name="worker_id"]');
                    workerDropdown.empty().append(
                        '<option value="">{{ __('field.select_branch_first') }}</option>');
                    if (selectedBranch === '') {
                        selectedBranch = '{{ $selected_branch }}';
                    }

                    $.ajax({
                        url: "{{ route('center_user.reports.get-users-by-branch', ['locale' => app()->getLocale()]) }}",
                        method: 'GET',
                        data: {
                            branch_id: selectedBranch
                        },
                        success: function(response) {
                            $.each(response.users, function(index, user) {
                                workerDropdown.append('<option value="' + user.id + '">' +
                                    user.name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                });
                $('select[name="branch_id"]').trigger('change');
            });
        </script>
    @endpush
@endsection
