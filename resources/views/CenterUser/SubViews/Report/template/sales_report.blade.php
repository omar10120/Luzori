<br>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 150px">{{ __('field.date') }}</th>
                @foreach ($payments_type as $index => $value)
                    <th>{{ $value }}</th>
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
                                if ($index != 'free' && $index != 'wallet') {
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
                    @php
                        $footer_total = 0;
                    @endphp
                    @foreach ($last_total as $index => $value)
                        @php
                            if ($index != 'total_without_free') {
                                $footer_total += $value;
                            }
                        @endphp
                    @endforeach
                    <strong>AED {{ number_format($footer_total, 2, '.', '') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
