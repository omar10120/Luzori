@extends('layouts/layoutMaster')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="caption">
                <i class="fa fa-plus"></i> {{ __('locale.daily_report') }}
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <form method="get" action="{{ route('center_user.reports.daily-report') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('field.date') }} </label>
                                <input type="date" value="{{ $date }}" name="date" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('field.branch') }} </label>
                                <select class="form-control" name="branch_id">
                                    @if (get_user_role() == 1 || get_user_role() == 3)
                                        <option value="">{{ __('field.all_branches') }} </option>
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
                                @if (isset($request) && !empty($request->has('date')))
                                    <a target="_blank"
                                        href="{{ route('center_user.reports.daily-report') }}?date={{ $date }}&branch_id={{ $selected_branch }}&is_pdf=true"
                                        class="btn btn-primary">PDF</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {!! $template !!}
                </div>
            </div>
        </div>
    </div>
@endsection
