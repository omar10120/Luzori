@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit" enctype="multipart/form-data">
                @csrf
                @if ($item)
                    <input type="hidden" name="id" value="{{ $item->id }}">
                @endif

                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="title">{{ __('field.title') }} <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="title" name="title" value="{{ $item?->title ?? '' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="placement_type">{{ __('field.placement') }}</label>
                                <select class="form-select" id="placement_type" name="placement_type" required>
                                    @foreach (['home', 'mobile', 'web', 'sidebar', 'checkout', 'category_page', 'service_page'] as $placement)
                                        <option value="{{ $placement }}" @selected(($item?->placement_type ?? 'home') === $placement)>
                                            {{ ucfirst(str_replace('_', ' ', $placement)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label" for="description">{{ __('field.description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3">{{ $item?->description ?? '' }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="link_type">{{ __('field.link_type') }}</label>
                                <select class="form-select" id="link_type" name="link_type" required>
                                    @foreach (['external', 'category', 'service', 'center'] as $type)
                                        <option value="{{ $type }}" @selected(($item?->link_type ?? 'external') === $type)>{{ ucfirst($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="sort_order">{{ __('field.sort_order') }}</label>
                                <input class="form-control" type="number" min="0" id="sort_order" name="sort_order" value="{{ $item?->sort_order ?? 0 }}">
                            </div>

                            <div class="col-12 mb-3" id="external_target">
                                <label class="form-label" for="external_url">{{ __('field.external_url') }}</label>
                                <input class="form-control" type="url" id="external_url" name="external_url" value="{{ $item?->external_url ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3 d-none" id="category_target">
                                <label class="form-label" for="global_category_id">{{ __('field.category') }}</label>
                                <select class="form-select" id="global_category_id" name="global_category_id">
                                    <option value="">{{ __('general.choose') }}</option>
                                    @foreach ($options['categories'] as $category)
                                        <option value="{{ $category->id }}" @selected((int) ($item?->global_category_id) === (int) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-none" id="center_target">
                                <label class="form-label" for="center_id">{{ __('field.center') }}</label>
                                <select class="form-select" id="center_id" name="center_id">
                                    <option value="">{{ __('general.choose') }}</option>
                                    @foreach ($options['centers'] as $center)
                                        <option value="{{ $center['id'] }}" @selected((int) ($item?->center_id) === (int) $center['id'])>{{ $center['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-none" id="service_target">
                                <label class="form-label" for="service_id">{{ __('field.service') }}</label>
                                <select class="form-select" id="service_id" name="service_id">
                                    <option value="">{{ __('general.choose') }}</option>
                                    @foreach ($options['centers'] as $center)
                                        @if (!empty($options['services'][$center['id']]))
                                            <optgroup label="{{ $center['name'] }}">
                                                @foreach ($options['services'][$center['id']] as $service)
                                                    <option value="{{ $service['id'] }}" data-center-id="{{ $center['id'] }}" @selected((int) ($item?->service_id) === (int) $service['id'] && (int) ($item?->center_id) === (int) $center['id'])>{{ $service['name'] }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="hidden" id="service_center_id" name="service_center_id" value="{{ $item?->center_id ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked($item?->is_active ?? true)>
                                    <label class="form-check-label" for="is_active">{{ __('field.active') }}</label>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                @include('Admin.Components.image', ['item' => $item, 'name' => 'image', 'model' => 'banner'])
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary me-2">{{ __('general.back') }}</a>
                        <button type="submit" class="btn btn-primary submitFrom">{{ __('general.save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    @include('Admin.Components.image-js')
    <script>
        $(function () {
            const $linkType = $('#link_type');
            const $service = $('#service_id');
            const $serviceCenter = $('#service_center_id');

            function updateTargetFields() {
                const type = $linkType.val();
                $('#external_target, #category_target, #center_target, #service_target').addClass('d-none');
                if (type === 'external') $('#external_target').removeClass('d-none');
                if (type === 'category') $('#category_target').removeClass('d-none');
                if (type === 'center') $('#center_target').removeClass('d-none');
                if (type === 'service') $('#service_target').removeClass('d-none');
            }

            $service.on('change', function () {
                const centerId = $(this).find(':selected').data('center-id') || '';
                $serviceCenter.val(centerId);
                if ($linkType.val() === 'service') {
                    $('#center_id').val(centerId);
                }
            });
            $linkType.on('change', updateTargetFields);
            updateTargetFields();
            $service.trigger('change');
        });
    </script>
    @include('Admin.Components.submit-form-ajax')
@endsection
