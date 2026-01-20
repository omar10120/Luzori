@extends('layouts/layoutMaster')

@section('content')
    <div class="portlet box blue-hoki">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-plus"></i>{{ __('locale.commission_report') }}
            </div>
        </div>
        <div class="portlet-body">
            <form method="get" action="{{ route('center_user.reports.commission-report') }}">
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
                            <select class="form-control" name="branch_id">
                                @if (get_user_role() == 1 || get_user_role() == 3)
                                    <option value="">{{ __('field.all_branches') }}</option>
                                @endif
                                @if (!empty($branches))
                                    @foreach ($branches as $branch)
                                        @php
                                            $selected = '';
                                            if ($branch->id == $selected_branch) {
                                                $selected = 'selected=""';
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
                            <label>&nbsp;</label><br>
                            <button class="btn btn-success">{{ __('field.view') }}</button>
                            @if (isset($request) && !empty($request->has('year')))
                                <a target="_blank"
                                    href="{{ route('center_user.reports.commission-report') }}?year={{ $request->get('year') }}&month={{ $request->get('month') }}&branch_id={{ $request->get('branch_id') }}&is_pdf=true"
                                    class="btn btn-primary">PDF</a>
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
@endsection
