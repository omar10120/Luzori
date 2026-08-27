@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="mb-1">{{ $order->order_number }}</h2>
                    <p class="text-muted mb-0">
                        @if($order->status === 'received')
                            <span class="badge bg-label-success">{{ __('field.received') }}</span>
                        @else
                            <span class="badge bg-label-warning">{{ __('field.ordered') }}</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('center_user.stockorders.index') }}" class="btn btn-outline-secondary">{{ __('general.close') }}</a>
                    @if($order->status === 'ordered')
                        <a href="{{ route('center_user.stockorders.receive', ['id' => $order->id]) }}" class="btn btn-primary">{{ __('general.receive') }}</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="text-muted small">{{ __('field.created') }}</div>
                        <div class="fw-semibold">{{ $order->created_at ?: '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">{{ __('field.expected') }}</div>
                        <div class="fw-semibold">{{ $order->expected_at?->format('Y-m-d') ?: '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">{{ __('field.deliver_from') }}</div>
                        <div class="fw-semibold">{{ $order->deliver_from }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">{{ __('locale.branches') }}</div>
                        <div class="fw-semibold">{{ $order->branch->name ?? '-' }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('field.name') }}</th>
                                <th>{{ __('field.ordered') }}</th>
                                <th>{{ __('field.received') }}</th>
                                <th>{{ __('field.unit_cost') }}</th>
                                <th>{{ __('field.total_cost') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->product->name ?? '-' }}</div>
                                        <small class="text-muted">
                                            @if($item->product?->primarySku?->sku)
                                                SKU: {{ $item->product->primarySku->sku }}
                                            @endif
                                        </small>
                                    </td>
                                    <td>{{ number_format((float) $item->order_qty, 2) }}</td>
                                    <td>{{ $item->received_qty !== null ? number_format((float) $item->received_qty, 2) : '-' }}</td>
                                    <td>{{ number_format((float) $item->unit_cost, 2) }} {{ get_currency() }}</td>
                                    <td class="fw-bold">{{ number_format((float) $item->line_total, 2) }} {{ get_currency() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">{{ __('field.no_data_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">{{ __('field.total_cost') }}</td>
                                <td class="fw-bold">{{ number_format((float) $order->total_cost, 2) }} {{ get_currency() }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
