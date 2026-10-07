@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">{{ $title }}</h2>
            </div>
            <div class="card-body">
                <div id="global-product-error" class="alertcenters alert-danger d-none" role="alert"></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="center_id" class="form-label">{{ __('locale.centers') }} <span class="text-danger">*</span></label>
                        <select id="center_id" class="form-select select2" data-select="true" required>
                            <option value="">{{ __('field.select_center') }}</option>
                            @foreach ($centers as $center)
                                <option value="{{ $center->id }}">{{ $center->domain }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="product_id" class="form-label">{{ __('locale.products') }} <span class="text-danger">*</span></label>
                        <select id="product_id" class="form-select select2" data-select="true" required>
                            <option value="">{{ __('field.select_center_first') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="sort_order" class="form-label">{{ __('field.sort_order') }}</label>
                        <input type="number" min="0" step="1" id="sort_order" class="form-control" value="0">
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('admin.global-products.index') }}" class="btn btn-secondary">{{ __('general.back') }}</a>
                <button type="button" class="btn btn-primary" id="save-global-product">
                    <i class="ti ti-check me-1"></i>{{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
<script>
$(function () {
    const productsUrlTemplate = @json($productsUrl);
    const storeUrl = @json($storeUrl);
    const centerPlaceholder = @json(__('field.select_center_first'));
    const productPlaceholder = @json(__('field.select_product'));
    const emptyProductsText = @json(__('field.no_data_found'));
    const dropdownParent = $('#center_id').closest('.card-body');
    let request = null;

    // --- Select2 init helper ---
    function initSelect($el, placeholder) {
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2({
            placeholder: placeholder,
            allowClear: true,
            width: '100%',
            dropdownParent: dropdownParent,
        });
    }

    // --- Initial setup ---
    initSelect($('#center_id'), $('#center_id').find('option:first').text());
    initSelect($('#product_id'), centerPlaceholder);
    $('#product_id').prop('disabled', true);

    // --- Center change: fetch products for that center ---
    $('#center_id').on('change', function () {
        const centerId = $(this).val();
        const $product = $('#product_id');

        $('#global-product-error').addClass('d-none').text('');

        // Reset product select (empty + disabled) while loading
        if ($product.hasClass('select2-hidden-accessible')) {
            $product.select2('destroy');
        }
        $product.empty().append(`<option value="">${centerPlaceholder}</option>`);
        $product.prop('disabled', true);
        initSelect($product, centerPlaceholder);

        if (!centerId) return;
        if (request && request.readyState !== 4) request.abort();

        request = $.getJSON(productsUrlTemplate.replace('__CENTER__', encodeURIComponent(centerId)))
            .done(function (response) {
                const products = response.data || [];

                if ($product.hasClass('select2-hidden-accessible')) {
                    $product.select2('destroy');
                }
                $product.empty();

                if (!products.length) {
                    $product.append(`<option value="">${emptyProductsText}</option>`);
                    initSelect($product, emptyProductsText);
                    $product.prop('disabled', true);
                    return;
                }

                $product.append('<option value=""></option>');
                products.forEach(function (product) {
                    const label = product.barcode
                        ? product.name + ' (' + product.barcode + ')'
                        : product.name;
                    $product.append(new Option(label, product.id, false, false));
                });

                $product.prop('disabled', false);
                initSelect($product, productPlaceholder);
            })
            .fail(function (xhr) {
                if (xhr.statusText === 'abort') return;
                const message = xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}';
                $('#global-product-error').removeClass('d-none').text(message);
            });
    });

    // --- Save ---
    $('#save-global-product').on('click', function () {
        const $button = $(this);
        const $error = $('#global-product-error');
        const centerId = $('#center_id').val();
        const productId = $('#product_id').val();
        $error.addClass('d-none').text('');

        if (!centerId || !productId) {
            $error.removeClass('d-none').text('{{ __('field.select_center_and_product') }}');
            return;
        }

        const originalHtml = $button.html();
        $button.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('general.loading') }}');

        $.ajax({
            url: storeUrl,
            type: 'POST',
            data: {
                _token: @json(csrf_token()),
                center_id: centerId,
                product_id: productId,
                sort_order: $('#sort_order').val() || 0
            },
            success: function (response) {
                const data = response.data || {};
                window.location.href = data.redirect_url || @json(route('admin.global-products.index'));
            },
            error: function (xhr) {
                const payload = xhr.responseJSON || {};
                const messages = payload.errors
                    ? Object.values(payload.errors).flat()
                    : [payload.message || '{{ __('admin.an_error_occurred') }}'];
                $error.removeClass('d-none').text(messages.join(' '));
                $button.prop('disabled', false).html(originalHtml);
            }
        });
    });
});
</script>
@endsection