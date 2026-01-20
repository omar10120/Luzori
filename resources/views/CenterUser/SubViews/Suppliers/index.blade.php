@extends('layouts/layoutMaster')

@section('title', $title ?? __('locale.suppliers'))

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
@endsection

@section('content')

    <div class="container">
        @include('CenterUser.Components.breadcrumbs')
        

        @if (\Session::has('success'))
            <div class="alert alert-success">
                <div>{!! \Session::get('success') !!}</div>
            </div>
        @endif
        @if (\Session::has('error'))
            <div class="alert alert-danger">
                <div>{!! \Session::get('error') !!}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title ?? __('locale.suppliers') }}</h2>
                    </div>
                    <div class="card-body">
                        @if(isset($dataTable))
                            {!! $dataTable->table(['class' => 'table table-bordered table-hover']) !!}
                        @else
                            <!-- Create/Edit Form -->
                            <form method="POST" action="{{ $requestUrl }}" enctype="multipart/form-data">
                                @csrf
                                @if($item)
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    @if($item->logo)
                                        <input type="hidden" name="old_logo" value="{{ $item->logo }}">
                                    @endif
                                @endif
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.name') }}</label>
                                            <input type="text" class="form-control" name="name"
                                                placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : '' }}" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.email') }}</label>
                                            <input type="email" class="form-control" name="email"
                                                placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : '' }}" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.phone_number') }}</label>
                                            <input type="text" class="form-control" name="phone"
                                                placeholder="{{ __('field.phone_number') }}" value="{{ $item ? $item->phone : '' }}" required />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">{{ __('field.logo') }}</label>
                                            <input type="file" class="form-control" name="logo" accept="image/*" id="logoInput" />
                                            @if($item && $item->logo)
                                                <div class="mt-2">
                                                    <img src="{{ $item->logo_url ?: asset('storage/' . $item->logo) }}" alt="Current Logo" class="img-thumbnail" id="currentLogo" style="width: 60px; height: 60px;">
                                                </div>
                                            @endif
                                            <div class="mt-2" id="logoPreview" style="display: none;">
                                                <img id="previewImg" alt="Logo Preview" class="img-thumbnail" style="width: 60px; height: 60px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="container mb-4">
                                    @include('Admin.Components.languages-tabs')
                                    <div class="tab-content">
                                        @foreach (Config::get('translatable.locales') as $locale)
                                            <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                                aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">{{ __('field.description') }} ({{ strtoupper($locale) }})</label>
                                                            <textarea name="{{ $locale }}[description]" id="description_{{ $locale }}" class="form-control" rows="3"
                                                                placeholder="{{ __('field.description') }}">{{ $item ? $item->translate($locale)->description : '' }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary submitFrom">
                                            <i class="ti ti-check"></i> {{ __('general.save') }}
                                        </button>
                                        <a href="{{ route('center_user.suppliers.index') }}" class="btn btn-secondary">
                                            <i class="ti ti-arrow-left"></i> {{ __('general.back') }}
                                        </a>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    @if(isset($dataTable))
        @include('CenterUser.Components.datatable-js')
        
        <script>
            // Handle description modal functionality
            $(document).on('click', '[data-bs-target^="#descModal"]', function() {
                var modalId = $(this).data('bs-target').replace('#descModal', '');
                var descText = $(this).data('description');
                
                // Check if modal already exists
                if ($('#descModal' + modalId).length === 0) {
                    // Create modal dynamically
                    var modalHtml = `
                        <div class="modal fade" id="descModal${modalId}" tabindex="-1" aria-labelledby="descModalLabel${modalId}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="descModalLabel${modalId}">Description</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p id="descContent${modalId}">${descText}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    $('body').append(modalHtml);
                } else {
                    // Update existing modal content
                    $('#descContent' + modalId).text(descText);
                }
                
                // Show the modal using Bootstrap 5
                var modal = new bootstrap.Modal(document.getElementById('descModal' + modalId));
                modal.show();
            });
        </script>
    @else
        @include('Admin.Components.image-js')
        @include('CenterUser.Components.translation-js')
        @include('CenterUser.Components.submit-form-ajax')
        
        <script>
            if (document.getElementById('logoInput')) {
                document.getElementById('logoInput').addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.getElementById('previewImg').src = e.target.result;
                            document.getElementById('logoPreview').style.display = 'block';
                            if (document.getElementById('currentLogo')) {
                                document.getElementById('currentLogo').style.display = 'none';
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        </script>
    @endif
@endsection
