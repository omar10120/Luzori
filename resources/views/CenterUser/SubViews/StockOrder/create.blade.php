@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="mb-1">{{ $title }}</h2>
                    <p class="text-muted mb-0">{{ __('general.select_products_to_add_to_order') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('center_user.stockorders.index') }}" class="btn btn-outline-secondary">{{ __('general.close') }}</a>
                    <button type="button" class="btn btn-primary" id="btnSaveOrder">{{ __('general.save') }}</button>
                </div>
            </div>
            <div class="card-body">
                <div id="alertError" class="alert alert-danger d-none" role="alert">
                    <ul id="listError" class="mb-0"></ul>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('locale.branches') }} <span class="text-danger">*</span></label>
                        <select id="branch_id" class="form-control select2" required>
                            <option value="">{{ __('field.select_branch') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('locale.suppliers') }} <span class="text-danger">*</span></label>
                        <select id="product_supplier_id" class="form-control select2" disabled required>
                            <option value="">{{ __('field.select_supplier') }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('field.expected') }}</label>
                        <input type="date" id="expected_at" class="form-control">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ __('locale.products') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnOpenProducts" disabled>
                        <i class="ti ti-plus me-1"></i>{{ __('general.add') }} {{ __('locale.products') }}
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="order-items-table">
                        <thead>
                            <tr>
                                <th>{{ __('field.product_name') }}</th>
                                <th style="width: 140px;">{{ __('field.order_qty') }}</th>
                                <th style="width: 160px;">{{ __('field.unit_cost') }}</th>
                                <th style="width: 140px;">{{ __('field.total_cost') }}</th>
                                <th style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody id="order-items-body">
                            <tr id="empty-items-row">
                                <td colspan="5" class="text-center text-muted py-4">{{ __('field.no_products_selected') }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">{{ __('field.total_cost') }}</td>
                                <td class="fw-bold" id="order-total-cost">0.00 {{ get_currency() }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Product picker modal --}}
    <div class="modal fade" id="addProductsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">{{ __('general.add') }} {{ __('locale.products') }}</h5>
                        <small class="text-muted">{{ __('general.select_products_to_add_to_order') }}</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" id="product-search" class="form-control" placeholder="{{ __('field.search_by_product_name_or_barcode') }}">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width:40px;"><input type="checkbox" id="check-all-products"></th>
                                    <th>{{ __('field.product_name') }}</th>
                                    <th>{{ __('field.category') }}</th>
                                    <th>{{ __('field.quantity') }}</th>
                                    <th>{{ __('field.product_cost') }}</th>
                                </tr>
                            </thead>
                            <tbody id="picker-products-body">
                                <tr><td colspan="5" class="text-center text-muted py-3">{{ __('general.loading') ?? '...' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('general.close') }}</button>
                    <button type="button" class="btn btn-primary" id="btnAddSelectedProducts">{{ __('general.add') }} {{ __('locale.products') }}</button>
                </div>
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
    const currency = @json(get_currency());
    const suppliersUrl = @json(route('center_user.stockorders.suppliers'));
    const productsUrl = @json(route('center_user.stockorders.products'));
    const saveUrl = @json($requestUrl);
    let selectedItems = {}; // product_id => {id,name,barcode,sku,order_qty,unit_cost}
    let pickerProducts = [];
    let searchTimer = null;

    $('.select2').select2({ width: '100%' });

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    function formatMoney(amount) {
        return Number(amount || 0).toFixed(2) + ' ' + currency;
    }

    function recalcTotals() {
        let total = 0;
        Object.values(selectedItems).forEach(function (item) {
            total += Number(item.order_qty || 0) * Number(item.unit_cost || 0);
        });
        $('#order-total-cost').text(formatMoney(total));
    }

    function renderOrderItems() {
        const $body = $('#order-items-body');
        const items = Object.values(selectedItems);
        if (!items.length) {
            $body.html(`<tr id="empty-items-row"><td colspan="5" class="text-center text-muted py-4">{{ __('field.no_products_selected') }}</td></tr>`);
            recalcTotals();
            return;
        }

        $body.html(items.map(function (item) {
            const lineTotal = Number(item.order_qty || 0) * Number(item.unit_cost || 0);
            return `
                <tr data-product-id="${item.id}">
                    <td>
                        <div class="fw-semibold">${escapeHtml(item.name)}</div>
                        <small class="text-muted">
                            ${item.sku ? 'SKU: ' + escapeHtml(item.sku) : ''}
                            ${item.barcode ? (item.sku ? ' · ' : '') + 'Barcode: ' + escapeHtml(item.barcode) : ''}
                        </small>
                    </td>
                    <td>
                        <input type="number" min="0.01" step="0.01" class="form-control form-control-sm item-order-qty" value="${item.order_qty}">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">${escapeHtml(currency)}</span>
                            <input type="number" min="0" step="0.01" class="form-control item-unit-cost" value="${item.unit_cost}">
                        </div>
                    </td>
                    <td class="item-line-total">${formatMoney(lineTotal)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="ti ti-trash"></i></button>
                    </td>
                </tr>
            `;
        }).join(''));
        recalcTotals();
    }

    function loadSuppliers(branchId) {
        const $supplier = $('#product_supplier_id');
        $supplier.prop('disabled', true).html(`<option value="">{{ __('field.select_supplier') }}</option>`).trigger('change');
        $('#btnOpenProducts').prop('disabled', true);
        selectedItems = {};
        renderOrderItems();

        if (!branchId) return;

        $.getJSON(suppliersUrl, { branch_id: branchId })
            .done(function (res) {
                const rows = res.data || [];
                rows.forEach(function (s) {
                    $supplier.append(`<option value="${s.id}">${escapeHtml(s.name)}</option>`);
                });
                $supplier.prop('disabled', false).trigger('change');
            });
    }

    function loadPickerProducts() {
        const branchId = $('#branch_id').val();
        const supplierId = $('#product_supplier_id').val();
        const q = $('#product-search').val();
        const $body = $('#picker-products-body');

        if (!branchId || !supplierId) {
            $body.html(`<tr><td colspan="5" class="text-center text-muted">{{ __('field.select_branch_and_supplier_first') }}</td></tr>`);
            return;
        }

        $body.html(`<tr><td colspan="5" class="text-center text-muted py-3">{{ __('general.loading') ?? 'Loading...' }}</td></tr>`);

        $.getJSON(productsUrl, { branch_id: branchId, supplier_id: supplierId, q: q })
            .done(function (res) {
                pickerProducts = res.data || [];
                if (!pickerProducts.length) {
                    $body.html(`<tr><td colspan="5" class="text-center text-muted">{{ __('field.no_data_found') }}</td></tr>`);
                    return;
                }

                $body.html(pickerProducts.map(function (p) {
                    const checked = selectedItems[p.id] ? 'checked' : '';
                    return `
                        <tr>
                            <td><input type="checkbox" class="picker-check" value="${p.id}" ${checked}></td>
                            <td>
                                <div class="fw-semibold">${escapeHtml(p.name)}</div>
                                <small class="text-muted">Barcode: ${escapeHtml(p.barcode || '-')}</small>
                            </td>
                            <td>${escapeHtml(p.category || '-')}</td>
                            <td>${Number(p.stock_quantity || 0)}</td>
                            <td>${formatMoney(p.supply_price)}</td>
                        </tr>
                    `;
                }).join(''));
            });
    }

    $('#branch_id').on('change', function () {
        loadSuppliers($(this).val());
    });

    $('#product_supplier_id').on('change', function () {
        const ready = !!$('#branch_id').val() && !!$(this).val();
        $('#btnOpenProducts').prop('disabled', !ready);
        selectedItems = {};
        renderOrderItems();
    });

    $('#btnOpenProducts').on('click', function () {
        $('#product-search').val('');
        $('#check-all-products').prop('checked', false);
        loadPickerProducts();
        $('#addProductsModal').modal('show');
    });

    $('#product-search').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadPickerProducts, 300);
    });

    $('#check-all-products').on('change', function () {
        $('.picker-check').prop('checked', $(this).is(':checked'));
    });

    $('#btnAddSelectedProducts').on('click', function () {
        $('.picker-check:checked').each(function () {
            const id = Number($(this).val());
            const product = pickerProducts.find(p => Number(p.id) === id);
            if (!product) return;
            if (!selectedItems[id]) {
                selectedItems[id] = {
                    id: product.id,
                    name: product.name,
                    barcode: product.barcode,
                    sku: product.sku,
                    order_qty: 1,
                    unit_cost: Number(product.supply_price || 0),
                };
            }
        });
        renderOrderItems();
        $('#addProductsModal').modal('hide');
    });

    $(document).on('input', '.item-order-qty, .item-unit-cost', function () {
        const $row = $(this).closest('tr');
        const id = Number($row.data('product-id'));
        if (!selectedItems[id]) return;
        selectedItems[id].order_qty = Number($row.find('.item-order-qty').val() || 0);
        selectedItems[id].unit_cost = Number($row.find('.item-unit-cost').val() || 0);
        const lineTotal = selectedItems[id].order_qty * selectedItems[id].unit_cost;
        $row.find('.item-line-total').text(formatMoney(lineTotal));
        recalcTotals();
    });

    $(document).on('click', '.btn-remove-item', function () {
        const id = Number($(this).closest('tr').data('product-id'));
        delete selectedItems[id];
        renderOrderItems();
    });

    $('#btnSaveOrder').on('click', function () {
        const $btn = $(this);
        const branchId = $('#branch_id').val();
        const supplierId = $('#product_supplier_id').val();
        const items = Object.values(selectedItems);

        $('#listError').empty();
        $('#alertError').addClass('d-none');

        if (!branchId || !supplierId || !items.length) {
            $('#listError').append(`<li>{{ __('field.please_complete_order_fields') }}</li>`);
            $('#alertError').removeClass('d-none');
            return;
        }

        $btn.prop('disabled', true);

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                branch_id: branchId,
                product_supplier_id: supplierId,
                expected_at: $('#expected_at').val() || null,
                items: items.map(function (item) {
                    return {
                        product_id: item.id,
                        order_qty: item.order_qty,
                        unit_cost: item.unit_cost,
                    };
                }),
            },
            success: function (response) {
                if (response.data && response.data.redirect_url) {
                    window.location.href = response.data.redirect_url;
                    return;
                }
                window.location.href = '{{ route('center_user.stockorders.index') }}';
            },
            error: function (xhr) {
                const payload = xhr.responseJSON || {};
                const messages = [];
                if (payload.errors) {
                    Object.values(payload.errors).forEach(function (arr) {
                        (Array.isArray(arr) ? arr : [arr]).forEach(function (msg) { messages.push(msg); });
                    });
                } else if (payload.message) {
                    messages.push(payload.message);
                } else {
                    messages.push('{{ __('admin.an_error_occurred') }}');
                }
                messages.forEach(function (msg) {
                    $('#listError').append($('<li>').text(msg));
                });
                $('#alertError').removeClass('d-none');
                $btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endsection
