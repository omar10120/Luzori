@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2>{{ $title }}</h2>
                        <div>
                            <a href="{{ route('center_user.sales.print', $sale->id) }}" class="btn btn-primary">
                                <i class="ti ti-printer me-1"></i>
                                {{ __('general.print') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>{{ __('field.sale_information') }}</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __('field.sale_id') }}:</strong></td>
                                        <td>#{{ $sale->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('field.date') }}:</strong></td>
                                        <td>{{ is_string($sale->created_at) ? substr($sale->created_at, 0, 16) : ($sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('field.worker') }}:</strong></td>
                                        <td>{{ $sale->worker?->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('field.client') }}:</strong></td>
                                        <td>{{ $sale->client?->name ?? __('general.walk_in') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('field.payment_method') }}:</strong></td>
                                        <td>{{ $sale->payment_type ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>{{ __('field.financial_summary') }}</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>{{ __('field.subtotal') }}:</strong></td>
                                        <td class="text-end">{{ number_format($sale->subtotal, 2) }} {{ get_currency() }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>{{ __('field.tax') }}:</strong></td>
                                        <td class="text-end">{{ number_format($sale->tax, 2) }} {{ get_currency() }}</td>
                                    </tr>
                                    @if($sale->tip > 0)
                                    <tr>
                                        <td><strong>{{ __('field.tip') }} ({{ $sale->worker?->name }}):</strong></td>
                                        <td class="text-end text-primary">{{ number_format($sale->tip, 2) }} {{ get_currency() }}</td>
                                    </tr>
                                    @endif
                                    <tr class="border-top">
                                        <td><strong class="fs-5">{{ __('field.total') }}:</strong></td>
                                        <td class="text-end"><strong class="fs-5 text-primary">{{ number_format($sale->total, 2) }} {{ get_currency() }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3">{{ __('field.items') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('field.type') }}</th>
                                        <th>{{ __('field.item') }}</th>
                                        <th>{{ __('field.quantity') }}</th>
                                        <th class="text-end">{{ __('field.price') }}</th>
                                        <th class="text-end">{{ __('field.subtotal') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->saleItems as $saleItem)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $saleItem->item_type === 'booking' ? 'primary' : 'info' }}">
                                                    {{ $saleItem->item_type === 'booking' ? __('locale.bookings') : __('locale.products') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($saleItem->item_type === 'booking')
                                                    @php
                                                        $booking = $saleItem->itemable;
                                                        $service = $booking->details->first()->service ?? null;
                                                    @endphp
                                                    {{ $service?->name ?? '-' }}
                                                    @if($booking->details->first())
                                                        <br><small class="text-muted">
                                                            {{ $booking->details->first()->_date }} • 
                                                            {{ $booking->details->first()->from_time }} - {{ $booking->details->first()->to_time }}
                                                        </small>
                                                    @endif
                                                @elseif($saleItem->item_type === 'buy_product')
                                                    @php
                                                        $buyProduct = $saleItem->itemable;
                                                        $products = $buyProduct->details->map(function($detail) {
                                                            return $detail->product?->name;
                                                        })->filter()->implode(', ');
                                                    @endphp
                                                    {{ $products ?: '-' }}
                                                @endif
                                            </td>
                                            <td>{{ $saleItem->quantity }}</td>
                                            <td class="text-end">{{ number_format($saleItem->price, 2) }} {{ get_currency() }}</td>
                                            <td class="text-end">{{ number_format($saleItem->subtotal, 2) }} {{ get_currency() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

