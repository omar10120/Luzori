@extends('layouts/layoutMaster')

@section('title', __('locale.tips_report'))

@section('vendor-style')
    @include('CenterUser.Components.report-page-styles')
@endsection

@section('content')
    <div class="luzori-page lz-report-page">
        @include('CenterUser.Components.breadcrumbs', [
            'menu' => __('locale.reports'),
            'menu_link' => null,
            'title' => __('locale.tips_report'),
        ])

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-coin"></i>
                </div>
                <h1 class="lz-page-title">{{ __('locale.tips_report') }}</h1>
            </div>
        </div>

        <div class="card lz-form-card">
            <div class="card-body">
                <div class="lz-report-filters">
                    <form method="get" action="{{ route('center_user.reports.tips-report') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="lz-field-label">{{ __('field.year') }}</label>
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
                            <div class="col-md-2">
                                <label class="lz-field-label">{{ __('field.month') }}</label>
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
                            <div class="col-md-2">
                                <label class="lz-field-label">{{ __('field.branch') }}</label>
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
                            <div class="col-md-3">
                                <label class="lz-field-label">{{ __('field.worker') }}</label>
                                <select class="form-control" name="worker_id" id="worker_id">
                                    <!-- Placeholder option -->
                                    <option value="">{{ __('field.select_worker_first') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="lz-report-actions">
                                    <button class="btn btn-luzori-primary">
                                        <i class="ti ti-eye me-1"></i>{{ __('field.view') }}
                                    </button>
                                    @if (isset($request) && !empty($request->has('year')))
                                        <a target="_blank"
                                            href="{{ route('center_user.reports.tips-report') }}?year={{ $request->get('year') }}&month={{ $request->get('month') }}&branch_id={{ $request->get('branch_id') }}&worker_id={{ $request->get('worker_id') }}&is_pdf=true"
                                            class="btn btn-luzori-gold">
                                            <i class="ti ti-file-type-pdf me-1"></i>PDF
                                        </a>
                                        @if ($selected_worker)
                                            <a target="_blank"
                                                href="{{ route('center_user.reports.tips-report') }}?year={{ $request->get('year') }}&month={{ $request->get('month') }}&branch_id={{ $request->get('branch_id') }}&worker_id={{ $request->get('worker_id') }}&is_print=true"
                                                class="btn btn-luzori-beige">
                                                <i class="ti ti-printer me-1"></i>{{ __('general.print') }}
                                            </a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="lz-report-result">
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
