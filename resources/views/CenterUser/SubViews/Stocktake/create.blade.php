@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                @if($item)
                    <input type="hidden" name="id" value="{{ $item->id }}">
                @endif
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2>{{ $title }}</h2>
                        <div>
                            <a href="{{ route('center_user.stocktakes.index') }}" class="btn btn-outline-secondary">Close</a>
                            @if(!$item || $item->status == 'draft') 
                                <button type="submit" class="btn btn-primary">Start stocktake</button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Start a full inventory count to keep accurate stock levels. <a href="#">Learn more</a></p>

                        @if($item && $item->branches->count() > 0)
                            <div class="alert alert-light border mb-4">
                                <div class="d-flex align-items-start">
                                    <i class="ti ti-building-store me-3" style="font-size: 24px; color: #696cff; margin-top: 4px;"></i>
                                    <div class="flex-grow-1">
                                        @foreach($item->branches as $branch)
                                            <div class="mb-2">
                                                <strong>{{ $branch->name }}</strong>
                                                <div class="text-muted small">
                                                    {{ $branch->translate(app()->getLocale())->city ?? '' }}, 
                                                    {{ $branch->translate(app()->getLocale())->address ?? '' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <h5 class="mb-3">Stocktake info</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Stocktake name (Optional)</label>
                                <small class="text-muted">{{__('general.enter_the_name_of_the_stocktake')}}</small>
                                <input type="text" class="form-control" name="name" 
                                    placeholder="Stocktake name"
                                    value="{{ $item ? $item->name : '' }}" />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Stocktake description (Optional)</label>
                                <textarea class="form-control" name="description" rows="3" 
                                    placeholder="Stocktake description" maxlength="200">{{ $item ? $item->description : '' }}</textarea>
                                <small class="text-muted"><span id="desc_counter">0</span>/200</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Select Branches <span class="text-danger">*</span></label>
                                <small class="text-muted">{{__('general.select_one_or_more_branches_to_count_stock_for')}}</small>
                                <select class="form-control select2" name="branch_ids[]" id="branch_ids" 
                                    data-select="true" multiple required>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" 
                                            {{ $item && $item->branches->contains($branch->id) ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: 'Select branches',
                    allowClear: true,
                    dropdownParent: $(this).parent()
                });
            });

            // Character counter
            $('textarea[name="description"]').on('input', function() {
                $('#desc_counter').text($(this).val().length);
            });
            $('#desc_counter').text($('textarea[name="description"]').val().length);
        });
    </script>
@endsection

