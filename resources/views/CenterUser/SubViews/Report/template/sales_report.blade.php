@php
    $fontFamily = App::getLocale() == 'ar' ? 'Cairo' : 'Poppins';
    $usedWalletAmountKeys = $usedWalletAmountKeys ?? [];
@endphp
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
<style>
     .daily-report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
        font-family: '{{ $fontFamily }}',
        sans-serif !important;
    }

    .daily-report-table td,
    .daily-report-table th {
        vertical-align: top;
    }

    .daily-report-table strong {
        font-weight: 300;
        font-size: 12px;
    }

    .table-responsive {

        font-size: 12px;
    }
</style>
<br>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 150px">{{ __('field.date') }}</th>
                @foreach ($payments_type as $index => $value)
                    <th>{{ __('field.'.$value) }}</th>
                @endforeach
                <th>{{ 'Total Without Free' }}</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($result))
                @foreach ($result as $date => $payments)
                    @php
                        $total = 0;
                    @endphp
                    <tr>
                        <td>{{ date('d-M-Y', strtotime($date)) }}</td>
                        @foreach ($payments_type as $index => $value)
                            @php
                                $excludeFromRowTotal = $index === 'free'
                                    || $index === 'wallet'
                                    || !empty($usedWalletAmountKeys[$index]);
                                if (!$excludeFromRowTotal) {
                                    $total += $result[$date][$index];
                                }
                                $last_total[$index] += $result[$date][$index];
                            @endphp
                            <td>{{ number_format($result[$date][$index], 2, '.', '') }}</td>
                        @endforeach
                        @php
                            $last_total['total_without_free'] += $total;
                        @endphp
                        <td>{{ number_format($total, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td style="background-color: #666;color: #fff;text-align: center"><strong>{{ 'Total' }}</strong>
                </td>
                @foreach ($last_total as $index => $value)
                    <td style="background-color: #666;color: #fff;text-align: center"><strong>AED
                            {{ number_format($value, 2, '.', '') }}</strong></td>
                @endforeach
            </tr>
            <tr>
                <td colspan="11" style="background-color: #666;color: #fff;text-align: center">
                    <strong>AED {{ number_format($last_total['total_without_free'] ?? 0, 2, '.', '') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
