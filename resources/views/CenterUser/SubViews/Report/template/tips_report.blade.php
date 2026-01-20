<br>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 70px">{{ 'Date' }}</th>
                @foreach ($firstusers as $index => $value)
                    <th>{{ $value->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if (!empty($result))
                @foreach ($result['tips'] as $date => $payments)
                    @php
                        $total = 0;
                    @endphp
                    <tr>
                        <td>{{ date('d-M-Y', strtotime($date)) }}</td>
                        @foreach ($firstusers as $index => $value)
                            @if (optional($vacationsWorkerIds)[$date])
                                @if (in_array($value->id, $vacationsWorkerIds[$date]))
                                    <td>
                                        <span style="background-color: yellow">OFF</span>
                                    </td>
                                    @continue
                                @endif
                            @endif
                            @php
                                if ($index != 'free') {
                                    $total += $result['tips'][$date][$value->id];
                                }

                                $firstusers_last_total[$value->id] += $result['tips'][$date][$value->id];
                            @endphp
                            <td>{{ number_format($result['tips'][$date][$value->id], 2, '.', '') }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td style="background-color: #666;color: #fff;text-align: center"><strong>{{ 'Total' }}</strong>
                </td>
                @foreach ($firstusers_last_total as $index => $value)
                    <td style="background-color: #666;color: #fff;text-align: center"><strong>AED
                            {{ number_format($value, 2, '.', '') }}</strong></td>
                @endforeach
            </tr>

            <tr>
                <td style="background-color: #666;color: #fff;text-align: center">
                    <strong>{{ $tips }}</strong>
                </td>
                @php
                    $total_ = 0;
                @endphp
                @foreach ($firstusers_last_total as $index => $value)
                    @php
                        $value_ = $value - ($tips / 100) * $value;
                        $total_ += $value_;
                    @endphp
                    <td style="background-color: #666;color: #fff;text-align: center"><strong>AED
                            {{ number_format($value_, 2, '.', '') }}</strong></td>
                @endforeach
            </tr>

            <tr>
                <td colspan="{{ count($firstusers) + 1 }}" style="background-color: #666;color: #fff;text-align: center">
                    @php
                        $footer_total = 0;
                    @endphp
                    @foreach ($firstusers_last_total as $index => $value)
                        @php
                            if ($index != 'total_without_free') {
                                $footer_total += $value;
                            }
                        @endphp
                    @endforeach
                    <strong>Total : AED {{ number_format($footer_total, 2, '.', '') }}</strong>
                </td>
            </tr>

            <tr>
                <td colspan="{{ count($firstusers) + 1 }}"
                    style="background-color: #666;color: #fff;text-align: center">

                    <strong>Total {{ $tips }}% : AED {{ number_format($total_, 2, '.', '') }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>
    <br>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 70px">{{ 'Date' }}</th>
                @foreach ($secondusers as $index => $value)
                    <th>{{ $value->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if (!empty($result))
                @foreach ($result['tips'] as $date => $payments)
                    <tr>
                        <td>{{ date('d-M-Y', strtotime($date)) }}</td>
                        @foreach ($secondusers as $index => $value)
                            @if (optional($vacationsWorkerIds)[$date])
                                @if (in_array($value->id, $vacationsWorkerIds[$date]))
                                    <td>
                                        <span style="background-color: yellow">OFF</span>
                                    </td>
                                    @continue
                                @endif
                            @endif
                            @php
                                if ($index != 'free') {
                                    $total += $result['tips'][$date][$value->id];
                                }
                                $secondusers_last_total[$value->id] += $result['tips'][$date][$value->id];

                            @endphp
                            <td>{{ number_format($result['tips'][$date][$value->id], 2, '.', '') }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td style="background-color: #666;color: #fff;text-align: center"><strong>{{ 'Total' }}</strong>
                </td>
                @foreach ($secondusers_last_total as $index => $value)
                    <td style="background-color: #666;color: #fff;text-align: center"><strong>AED
                            {{ number_format($value, 2, '.', '') }}</strong></td>
                @endforeach
            </tr>
            <tr>
                <td style="background-color: #666;color: #fff;text-align: center"><strong> Total
                        {{ $tips }}% </strong>
                </td>
                @php
                    $total_ = 0;
                @endphp
                @foreach ($secondusers_last_total as $index => $value)
                    @php
                        $value_ = $value - ($tips / 100) * $value;
                        $total_ += $value_;
                    @endphp
                    <td style="background-color: #666;color: #fff;text-align: center"><strong>AED
                            {{ number_format($value_, 2, '.', '') }}</strong></td>
                @endforeach
            </tr>
            <tr>
                <td colspan="{{ count($secondusers) + 1 }}"
                    style="background-color: #666;color: #fff;text-align: center">
                    @foreach ($secondusers_last_total as $index => $value)
                        @php
                            if ($index != 'total_without_free') {
                                $footer_total += $value;
                            }
                        @endphp
                    @endforeach
                    <strong>AED {{ number_format($footer_total, 2, '.', '') }}</strong>
                </td>
            <tr>
                <td colspan="{{ count($firstusers) + 1 }}"
                    style="background-color: #666;color: #fff;text-align: center">

                    <strong>Total {{ $tips }} % : AED
                        {{ number_format($total_, 2, '.', '') }}</strong>

                </td>
            </tr>
            </tr>
        </tfoot>
    </table>
    <br>
    @if ($restusers != '[]')
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width: 70px">{{ 'Date' }}</th>
                    @foreach ($restusers as $index => $value)
                        <th>{{ $value->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if (!empty($result))
                    @foreach ($result['tips'] as $date => $payments)
                        <tr>
                            <td>{{ date('d-M-Y', strtotime($date)) }}</td>
                            @foreach ($restusers as $index => $value)
                                @if (optional($vacationsWorkerIds)[$date])
                                    @if (in_array($value->id, $vacationsWorkerIds[$date]))
                                        <td>
                                            <span style="background-color: yellow">OFF</span>
                                        </td>
                                        @continue
                                    @endif
                                @endif
                                @php
                                    if ($index != 'free') {
                                        $total += $result['tips'][$date][$value->id];
                                    }
                                    $restusers_last_total[$value->id] += $result['tips'][$date][$value->id];
                                @endphp
                                <td>{{ number_format($result['tips'][$date][$value->id], 2, '.', '') }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td style="background-color: #666;color: #fff;text-align: center">
                        <strong>{{ 'Total' }}</strong>
                    </td>
                    @foreach ($restusers_last_total as $index => $value)
                        <td style="background-color: #666;color: #fff;text-align: center"><strong>AED
                                {{ number_format($value, 2, '.', '') }}</strong></td>
                    @endforeach
                </tr>
                <tr>
                    <td colspan="{{ count($restusers) + 1 }}"
                        style="background-color: #666;color: #fff;text-align: center">
                        @if (isset($lasrestusers_last_totalt_total))
                            @foreach ($lasrestusers_last_totalt_total as $index => $value)
                                @php
                                    if ($index != 'total_without_free') {
                                        $footer_total += $value;
                                    }
                                @endphp
                            @endforeach
                        @endif
                        <strong>AED {{ number_format($footer_total, 2, '.', '') }}</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif
</div>
