<table class="table table-bordered">
    <thead>
        <tr>
            <th class="fw-bolder" scope="col">{{__('field.services')}}</th>
            <th class="fw-bolder" scope="col">{{__('field.price')}}</th>
            <th class="fw-bolder" scope="col">{{__('field.date')}}</th>
            <th class="fw-bolder" scope="col">{{__('field.worker')}}</th>
            <th class="fw-bolder" scope="col">{{__('field.from')}}</th>
            <th class="fw-bolder" scope="col">{{__('field.to')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            @if(($item['type'] ?? '') === 'service')
                <tr>
                    <td>
                        {{ $item['name'] }}
                        @if(!empty($item['discount_note']))
                            <div class="small text-success">{{ $item['discount_note'] }}</div>
                        @endif
                    </td>
                    <td>
                        @if(isset($item['original_price']) && $item['original_price'] > $item['price'])
                            <del class="text-muted small d-block">{{ number_format($item['original_price'], 2) }}</del>
                        @endif
                        <span class="fw-bold">{{ number_format($item['price'], 2) }} {{ get_currency() }}</span>
                    </td>
                    <td>{{ $item['date'] }}</td>
                    <td>{{ $item['worker_name'] ?? 'N/A' }}</td>
                    <td>{{ $item['from_time'] }}</td>
                    <td>{{ $item['to_time'] }}</td>
                </tr>
            @endif
        @endforeach

        @php
            $packages = array_filter($items, function($i) { return ($i['type'] ?? '') === 'package'; });
        @endphp
        
        @foreach($packages as $pkg)
            <tr class="table-info">
                <td><i class="ti ti-package me-1"></i> {{ $pkg['name'] }} (New Purchase)</td>
                <td class="fw-bold">{{ number_format($pkg['price'], 2) }} {{ get_currency() }}</td>
                <td colspan="4" class="text-center">{{ __('field.package_does_not_need_schedule') ?? 'No Scheduling Needed' }}</td>
            </tr>
        @endforeach

        <tr>
            <th class="fw-bolder" scope="row">{{__('field.full_name')}}</th>
            <td colspan="5">{{ $client_name ?? 'Walk-in' }}</td>
        </tr>
        <tr>
            <th class="fw-bolder" scope="row">{{__('field.mobile')}}</th>
            <td colspan="5">{{ $client_mobile ?? '-' }}</td>
        </tr>
        <tr>
            <th class="fw-bolder" scope="row">{{__('field.payment_method')}}</th>
            <td colspan="5">{{ $payment_method_display ?? '-' }}</td>
        </tr>
        
        <tr class="table-active">
            <th class="fw-bolder" scope="row">{{__('field.subtotal')}}</th>
            <td colspan="5" class="fw-bolder text-primary fs-5">{{ number_format($total, 2) }} {{ get_currency() }}</td>
        </tr>
    </tbody>
</table>
