<br>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 70px">{{ __('field.date') }}</th>
                @foreach ($firstusers as $index => $value)
                    <th>{{ $value->name }}</th>
                @endforeach
                <th>{{ __('field.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($result))
                @foreach ($result as $date => $commissions)
                    @php
                        $total = 0;
                    @endphp
                    <tr>
                        <td>{{ date('d-M-Y', strtotime($date)) }}</td>
                        @foreach ($firstusers as $index => $value)
                            @php
                                $total += $result[$date][$value->id];
                            @endphp
                            <td>{{ number_format($result[$date][$value->id], 2, '.', '') }}</td>
                        @endforeach
                        <td>{{ $result[$date]['total'] }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td style="background-color: #666;color: #fff;"><strong>{{ 'Total' }}</strong>
                </td>
                @php
                    $total = 0;
                @endphp
                @foreach ($firstusers as $index => $value)
                    @php
                        $total += $users_with_totals[$value->id];
                    @endphp
                    <td style="background-color: #666;color: #fff;"><strong>
                            {{ number_format($users_with_totals[$value->id], 2, '.', '') }}</strong></td>
                @endforeach
                <td style="background-color: #666;color: #fff;text-align: center"></td>
            </tr>
            <tr>
                <td style="background-color: #666;color: #fff;text-align: center"></td>
                <td style="background-color: #666;color: #fff;text-align: center;font-weight:bold;"
                    colspan="{{ count($firstusers) }}">
                    {{ number_format($total, 2, '.', '') }}
                </td>
                <td style="background-color: #666;color: #fff;text-align: center"></td>
            </tr>
        </tfoot>
    </table>
    <br>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 70px">{{ __('field.date') }}</th>
                @foreach ($secondusers as $index => $value)
                    <th>{{ $value->name }}</th>
                @endforeach
                <th>{{ __('field.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($result))
                @foreach ($result as $date => $commissions)
                    <tr>
                        <td>{{ date('d-M-Y', strtotime($date)) }}</td>
                        @foreach ($secondusers as $index => $value)
                            @php
                                $total += $result[$date][$value->id];
                            @endphp
                            <td>{{ number_format($result[$date][$value->id], 2, '.', '') }}</td>
                        @endforeach
                        <td>{{ $result[$date]['total'] }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td style="background-color: #666;color: #fff;"><strong>{{ 'Total' }}</strong>
                </td>
                @php
                    $total = 0;
                @endphp
                @foreach ($secondusers as $index => $value)
                    @php
                        $total += $users_with_totals[$value->id];
                    @endphp
                    <td style="background-color: #666;color: #fff;"><strong>
                            {{ number_format($users_with_totals[$value->id], 2, '.', '') }}</strong></td>
                @endforeach
                <td style="background-color: #666;color: #fff;text-align: center"></td>
            </tr>
            <tr>
                <td style="background-color: #666;color: #fff;text-align: center"></td>
                <td style="background-color: #666;color: #fff;text-align: center;font-weight:bold;"
                    colspan="{{ count($secondusers) }}">
                    {{ number_format($total, 2, '.', '') }}
                </td>
                <td style="background-color: #666;color: #fff;text-align: center"></td>
            </tr>
        </tfoot>
    </table>
    <br>

    @if ($restusers != '[]')
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 70px">{{ __('field.date') }}</th>
                    @foreach ($restusers as $index => $value)
                        <th>{{ $value->name }}</th>
                    @endforeach
                    <th>{{ __('field.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($result))
                    @foreach ($result as $date => $commissions)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($date)) }}</td>
                            @foreach ($restusers as $index => $value)
                                @php
                                    $total += $result[$date][$value->id];
                                @endphp
                                <td>{{ number_format($result[$date][$value->id], 2, '.', '') }}</td>
                            @endforeach
                            <td>{{ $result[$date]['total'] }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td style="background-color: #666;color: #fff;"><strong>{{ 'Total' }}</strong>
                    </td>
                    @foreach ($restusers as $index => $value)
                        @php
                            $total += $users_with_totals[$value->id];
                        @endphp
                        <td style="background-color: #666;color: #fff;"><strong>
                                {{ number_format($users_with_totals[$value->id], 2, '.', '') }}</strong></td>
                    @endforeach
                    <td style="background-color: #666;color: #fff;text-align: center"></td>
                </tr>
                <tr>
                    <td style="background-color: #666;color: #fff;text-align: center"></td>
                    <td style="background-color: #666;color: #fff;text-align: center;font-weight:bold;"
                        colspan="{{ count($restusers) }}">
                        {{ number_format($total, 2, '.', '') }}
                    </td>
                    <td style="background-color: #666;color: #fff;text-align: center"></td>
                </tr>
            </tfoot>
        </table>
    @endif
</div>
