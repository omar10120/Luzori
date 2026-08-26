@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="mb-1">{{ __('general.receive_products_in_stock_order') }}</h2>
                    <p class="text-muted mb-0">{{ $order->order_number }} · {{ $order->deliver_from }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('center_user.stockorders.index') }}" class="btn btn-outline-secondary">{{ __('general.close') }}</a>
                    <button type="button" class="btn btn-primary" id="btnReceiveOrder">{{ __('general.receive') }}</button>
                </div>
            </div>
            <div class="card-body">
                <div id="alertError" class="alert alert-danger d-none" role="alert">
                    <ul id="listError" class="mb-0"></ul>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="receive-items-table">
                        <thead>
                            <tr>
                                <th>{{ __('field.name') }}</th>
                                <th style="width: 100px;">{{ __('field.ordered') }}</th>
                                <th style="width: 140px;">{{ __('field.received') }}</th>
                                <th style="width: 180px;">{{ __('field.unit_cost') }}</th>
                                <th style="width: 140px;">{{ __('field.total_cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr data-item-id="{{ $item->id }}">
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name ?? '-' }}</div>
                                        <small class="text-muted">
                                            @if($item->product?->primarySku?->sku)
                                                SKU: {{ $item->product->primarySku->sku }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>{{ number_format((float) $item->order_qty, 2) }}</td>
                                    <td>
                                        <input type="number" min="0" step="0.01" class="form-control form-control-sm receive-qty"
                                               value="{{ number_format((float) $item->order_qty, 2, '.', '') }}">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{{ get_currency() }}</span>
                                            <input type="number" min="0" step="0.01" class="form-control receive-unit-cost"
                                                   value="{{ number_format((float) $item->unit_cost, 2, '.', '') }}">
                                        </div>
                                    </td>
                                    <td class="fw-bold receive-line-total">
                                        {{ number_format((float) $item->order_qty * (float) $item->unit_cost, 2) }} {{ get_currency() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">{{ __('field.total_cost') }}</td>
                                <td class="fw-bold" id="receive-total-cost">
                                    {{ number_format((float) $order->total_cost, 2) }} {{ get_currency() }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
$(function () {
    const currency = @json(get_currency());
    const saveUrl = @json($requestUrl);

    function formatMoney(amount) {
        return Number(amount || 0).toFixed(2) + ' ' + currency;
    }

    function recalc() {
        let total = 0;
        $('#receive-items-table tbody tr').each(function () {
            const qty = Number($(this).find('.receive-qty').val() || 0);
            const cost = Number($(this).find('.receive-unit-cost').val() || 0);
            const line = qty * cost;
            total += line;
            $(this).find('.receive-line-total').text(formatMoney(line));
        });
        $('#receive-total-cost').text(formatMoney(total));
    }

    $(document).on('input', '.receive-qty, .receive-unit-cost', recalc);

    $('#btnReceiveOrder').on('click', function () {
        const $btn = $(this);
        const items = [];

        $('#receive-items-table tbody tr').each(function () {
            items.push({
                id: Number($(this).data('item-id')),
                received_qty: Number($(this).find('.receive-qty').val() || 0),
                unit_cost: Number($(this).find('.receive-unit-cost').val() || 0),
            });
        });

        $('#listError').empty();
        $('#alertError').addClass('d-none');
        $btn.prop('disabled', true);

        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                items: items,
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
