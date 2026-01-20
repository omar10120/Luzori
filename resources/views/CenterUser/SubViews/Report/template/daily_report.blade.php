<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th colspan="{{ count($firstusers) }}">{{ __('admin.daily_report_for') . $date }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach ($firstusers as $user)
                    <td style="text-align: center"><strong>{{ $user->name }}</strong></td>
                @endforeach
            </tr>
            @php
                $total_with_users = [];
            @endphp
            <tr>
                @foreach ($firstusers as $user)
                    @php
                        $user_total = 0;
                    @endphp
                    <td>
                        @if (isset($users_with_prices[$user->id]))
                            @foreach ($users_with_prices[$user->id] as $with_price)
                                @if (in_array($user->id, $vacationsWorkerIds))
                                    <span style="background-color: yellow">OFF</span>
                                    @continue
                                @endif
                                @php
                                    $user_total += $with_price;
                                @endphp
                                {{ get_num_format($with_price) }}<br>
                            @endforeach
                            @php
                                $total_with_users[$user->id] = $user_total;
                            @endphp
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                @foreach ($firstusers as $user)
                    <td style="padding: 15px"></td>
                @endforeach
            </tr>
            <tr>
                @foreach ($firstusers as $user)
                    <td>
                        @if (in_array($user->id, $vacationsWorkerIds))
                            <span style="background-color: yellow">OFF</span>
                            @continue
                        @endif
                        @if (isset($total_with_users[$user->id]))
                            <strong>{{ get_currency() . get_num_format($total_with_users[$user->id]) }}</strong>
                        @endif
                    </td>
                @endforeach
            </tr>
            @php
                $total_payments_types = [];
            @endphp
            @if (!empty($payments_type))
                @foreach ($payments_type as $index => $value)
                    @if ($index != 'tips_visa')
                        @php
                            $total_with_users = [];
                        @endphp
                        @if (isset($payments_with_prices[$index]) && !empty($payments_with_prices[$index]))
                            <tr>
                                <td colspan="{{ count($firstusers) }}">
                                    <strong>{{ $value }}</strong>
                                </td>
                            </tr>
                            <tr>
                                @foreach ($firstusers as $user)
                                    @php
                                        $user_total = 0;
                                    @endphp
                                    <td>
                                        @if (in_array($user->id, $vacationsWorkerIds))
                                            <span style="background-color: yellow">OFF</span>
                                            @continue
                                        @endif
                                        @if (isset($payments_with_prices[$index][$user->id]))
                                            @foreach ($payments_with_prices[$index][$user->id] as $with_price)
                                                @php
                                                    $productPaymentMethods = get_payment_method_names('product');
                                                    $isProductPayment = in_array($index, $productPaymentMethods);
                                                @endphp
                                                @if ($isProductPayment && is_array($with_price))
                                                    @php
                                                        $user_total += $with_price['amount'];
                                                        if (isset($total_payments_types[$index])) {
                                                            $total_payments_types[$index] += $with_price['amount'];
                                                        } else {
                                                            $total_payments_types[$index] = $with_price['amount'];
                                                        }
                                                        $products = $with_price['products'];
                                                    @endphp
                                                    @foreach ($products as $product)
                                                        <div>
                                                            {{ $product->product->name }}
                                                            <br>
                                                            {{ $product->product->price }}
                                                        </div>
                                                        @if (!$loop->last)
                                                            <hr style="margin-top:5px;margin-bottom:5px;">
                                                        @endif
                                                    @endforeach
                                                    <br>
                                                @else
                                                    @php
                                                        $with_price_sum = is_array($with_price)
                                                            ? $with_price['amount']
                                                            : $with_price;
                                                        $user_total += $with_price_sum;
                                                    @endphp
                                                    @if (is_array($with_price))
                                                        <div
                                                            style="color: {{ get_color_type($with_price['type']) }};border-bottom: 1px solid">
                                                            @if (optional($with_price)['details'])
                                                                @foreach ($with_price['details'] as $key => $amountItem)
                                                                    <span>
                                                                        {{ $amountItem }}
                                                                        {{ get_symbol_type($with_price['type']) }}
                                                                        @if (!empty($with_price['codesArr'][$key]))
                                                                            <br>{{ $with_price['codesArr'][$key] }}
                                                                        @endif
                                                                        <br>
                                                                        {{ $with_price['detailsArr'][$key]->full_name }}
                                                                        <br>
                                                                        {{ $with_price['detailsArr'][$key]->mobile }}
                                                                    </span>
                                                                    @if (!$loop->last)
                                                                        <hr style="margin-top:5px;margin-bottom:5px;">
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                {{ $with_price['amount'] }}
                                                                {{ get_symbol_type($with_price['type']) }}
                                                                @if (!empty($with_price['code']))
                                                                    <br> {{ $with_price['code'] }}
                                                                @endif
                                                                <br>{{ $with_price['client_name'] }}
                                                            @endif
                                                        </div>
                                                    @else
                                                        {{ get_num_format($with_price) }}
                                                    @endif
                                                    <br>
                                                    @php
                                                        if (isset($total_payments_types[$index])) {
                                                            $total_payments_types[$index] += $with_price_sum;
                                                        } else {
                                                            $total_payments_types[$index] = $with_price_sum;
                                                        }
                                                    @endphp
                                                @endif
                                            @endforeach
                                            @php
                                                $total_with_users[$user->id] = $user_total;
                                            @endphp
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($firstusers as $user)
                                    <td>
                                        @if (in_array($user->id, $vacationsWorkerIds))
                                            <span style="background-color: yellow">OFF</span>
                                            @continue
                                        @endif
                                        @if (isset($total_with_users[$user->id]))
                                            <strong>{{ get_currency() . get_num_format($total_with_users[$user->id]) }}</strong>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endif
                @endforeach
            @endif
            @php
                $total_commission = 0;
            @endphp
            <tr>
                <td colspan="{{ count($firstusers) }}">
                    <strong>{{ __('field.commission') }}</strong>
                </td>
            </tr>
            @if (!empty($users_with_commission))
                <tr>
                    @foreach ($firstusers as $firstuser)
                        @if (in_array($firstuser->id, array_keys($users_with_commission)))
                            <td>
                                @if (!empty($users_with_commission[$firstuser->id]))
                                    @foreach ($users_with_commission[$firstuser->id] as $commission_value)
                                        @php
                                            $total_commission += $commission_value;
                                        @endphp
                                        {{ get_num_format($commission_value) }}<br>
                                    @endforeach
                                @else
                                    0.00
                                @endif
                            </td>
                        @else
                            <td> 0.00 </td>
                        @endif
                    @endforeach
                </tr>
            @endif
            @php
                $total_tips = 0;
            @endphp
            <tr>
                <td colspan="{{ count($firstusers) }}">
                    <strong>{{ __('field.tip') }}</strong>
                </td>
            </tr>
            @if (!empty($users_with_tips))
                <tr>
                    @foreach ($firstusers as $firstuser)
                        @if (in_array($firstuser->id, array_keys($users_with_tips)))
                            <td>
                                @if (!empty($users_with_tips[$firstuser->id]))
                                    @foreach ($users_with_tips[$firstuser->id] as $tips_value)
                                        @php
                                            $total_tips += $tips_value;
                                        @endphp
                                        {{ get_num_format($tips_value) }}<br>
                                    @endforeach
                                @else
                                    0.00
                                @endif
                            </td>
                        @else
                            <td> 0.00 </td>
                        @endif
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>

    @if ($secondusers != '[]')
        <br><br><br><br><br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="{{ count($secondusers) }}">{{ __('admin.daily_report_for') . $date }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach ($secondusers as $user)
                        <td style="text-align: center"><strong>{{ $user->name }}</strong></td>
                    @endforeach
                </tr>
                @php
                    $total_with_users = [];
                @endphp
                <tr>
                    @foreach ($secondusers as $user)
                        @php
                            $user_total = 0;
                        @endphp
                        <td>
                            @if (isset($users_with_prices[$user->id]))
                                @foreach ($users_with_prices[$user->id] as $with_price)
                                    @if (in_array($user->id, $vacationsWorkerIds))
                                        <span style="background-color: yellow">OFF</span>
                                        @continue
                                    @endif
                                    @php
                                        $user_total += $with_price;
                                    @endphp
                                    {{ get_num_format($with_price) }}<br>
                                @endforeach
                                @php
                                    $total_with_users[$user->id] = $user_total;
                                @endphp
                            @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($secondusers as $user)
                        <td style="padding: 15px"></td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($secondusers as $user)
                        <td>
                            @if (in_array($user->id, $vacationsWorkerIds))
                                <span style="background-color: yellow">OFF</span>
                                @continue
                            @endif
                            @if (isset($total_with_users[$user->id]))
                                <strong>{{ get_currency() . get_num_format($total_with_users[$user->id]) }}</strong>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @if (!empty($payments_type))
                    @foreach ($payments_type as $index => $value)
                        @if ($index != 'tips_visa')
                            @php
                                $total_with_users = [];
                            @endphp
                            @if (isset($payments_with_prices[$index]) && !empty($payments_with_prices[$index]))
                                <tr>
                                    <td colspan="{{ count($secondusers) }}">
                                        <strong>{{ $value }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    @foreach ($secondusers as $user)
                                        @php
                                            $user_total = 0;
                                        @endphp
                                        <td>
                                            @if (in_array($user->id, $vacationsWorkerIds))
                                                <span style="background-color: yellow">OFF</span>
                                                @continue
                                            @endif
                                            @if (isset($payments_with_prices[$index][$user->id]))
                                                @foreach ($payments_with_prices[$index][$user->id] as $with_price)
                                                    @php
                                                    $productPaymentMethods = get_payment_method_names('product');
                                                    $isProductPayment = in_array($index, $productPaymentMethods);
                                                @endphp
                                                @if ($isProductPayment)
                                                        @php
                                                            $user_total += $with_price['amount'];
                                                            if (isset($total_payments_types[$index])) {
                                                                $total_payments_types[$index] += $with_price['amount'];
                                                            } else {
                                                                $total_payments_types[$index] = $with_price['amount'];
                                                            }
                                                            $products = $with_price['products'];
                                                        @endphp
                                                        @foreach ($products as $product)
                                                            <div>
                                                                {{ $product->product->name }}
                                                                <br>
                                                                {{ $product->product->price }}
                                                            </div>
                                                            @if (!$loop->last)
                                                                <hr style="margin-top:5px;margin-bottom:5px;">
                                                            @endif
                                                        @endforeach
                                                        <br>
                                                    @else
                                                        @php
                                                            $with_price_sum = is_array($with_price)
                                                                ? $with_price['amount']
                                                                : $with_price;
                                                            $user_total += $with_price_sum;
                                                        @endphp
                                                        @if (is_array($with_price))
                                                            <div style="color: {{ get_color_type($with_price['type']) }};border-bottom: 1px solid">
                                                                @if (optional($with_price)['details'])
                                                                    @foreach ($with_price['details'] as $key => $amountItem)
                                                                        <span>
                                                                            {{ $amountItem }}
                                                                            {{ get_symbol_type($with_price['type']) }}
                                                                            @if (!empty($with_price['codesArr'][$key]))
                                                                                <br>{{ $with_price['codesArr'][$key] }}
                                                                            @endif
                                                                            <br>
                                                                            {{ $with_price['detailsArr'][$key]->full_name }}
                                                                            <br>
                                                                            {{ $with_price['detailsArr'][$key]->mobile }}
                                                                        </span>
                                                                        @if (!$loop->last)
                                                                            <hr
                                                                                style="margin-top:5px;margin-bottom:5px;">
                                                                        @endif
                                                                    @endforeach
                                                                @else
                                                                    {{ $with_price['amount'] }}
                                                                    {{ get_symbol_type($with_price['type']) }}
                                                                    @if (!empty($with_price['code']))
                                                                        <br> {{ $with_price['code'] }}
                                                                    @endif
                                                                    <br>{{ $with_price['client_name'] }}
                                                                @endif
                                                            </div>
                                                        @else
                                                            {{ get_num_format($with_price) }}
                                                        @endif
                                                        <br>
                                                        @php
                                                            if (isset($total_payments_types[$index])) {
                                                                $total_payments_types[$index] += $with_price_sum;
                                                            } else {
                                                                $total_payments_types[$index] = $with_price_sum;
                                                            }
                                                        @endphp
                                                    @endif
                                                @endforeach
                                                @php
                                                    $total_with_users[$user->id] = $user_total;
                                                @endphp
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach ($secondusers as $user)
                                        <td>
                                            @if (in_array($user->id, $vacationsWorkerIds))
                                                <span style="background-color: yellow">OFF</span>
                                                @continue
                                            @endif
                                            @if (isset($total_with_users[$user->id]))
                                                <strong>{{ get_currency() . get_num_format($total_with_users[$user->id]) }}</strong>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endif
                    @endforeach
                @endif
                <tr>
                    <td colspan="{{ count($secondusers) }}">
                        <strong>{{ __('field.commission') }}</strong>
                    </td>
                </tr>
                @if (!empty($users_with_commission))
                    <tr>
                        @foreach ($secondusers as $seconduser)
                            @if (in_array($seconduser->id, array_keys($users_with_commission)))
                                <td>
                                    @if (!empty($users_with_commission[$seconduser->id]))
                                        @foreach ($users_with_commission[$seconduser->id] as $commission_value)
                                            @php
                                                $total_commission += $commission_value;
                                            @endphp
                                            {{ get_num_format($commission_value) }}<br>
                                        @endforeach
                                    @else
                                        0.00
                                    @endif
                                </td>
                            @else
                                <td> 0.00 </td>
                            @endif
                        @endforeach
                    </tr>
                @endif
                <tr>
                    <td colspan="{{ count($secondusers) }}">
                        <strong>{{ __('field.tip') }}</strong>
                    </td>
                </tr>
                @if (!empty($users_with_tips))
                    <tr>
                        @foreach ($secondusers as $seconduser)
                            @if (in_array($seconduser->id, array_keys($users_with_tips)))
                                <td>
                                    @if (!empty($users_with_tips[$seconduser->id]))
                                        @foreach ($users_with_tips[$seconduser->id] as $tips_value)
                                            @php
                                                $total_tips += $tips_value;
                                            @endphp
                                            {{ get_num_format($tips_value) }}<br>
                                        @endforeach
                                    @else
                                        0.00
                                    @endif
                                </td>
                            @else
                                <td> 0.00 </td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    @if ($restusers != '[]')
        <br><br><br><br><br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="{{ count($restusers) }}">{{ __('admin.daily_report_for') . $date }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach ($restusers as $user)
                        <td style="text-align: center"><strong>{{ $user->name }}</strong></td>
                    @endforeach
                </tr>
                @php
                    $total_with_users = [];
                @endphp
                <tr>
                    @foreach ($restusers as $user)
                        @php
                            $user_total = 0;
                        @endphp
                        <td>
                            @if (isset($users_with_prices[$user->id]))
                                @foreach ($users_with_prices[$user->id] as $with_price)
                                    @if (in_array($user->id, $vacationsWorkerIds))
                                        <span style="background-color: yellow">OFF</span>
                                        @continue
                                    @endif
                                    @php
                                        $user_total += $with_price;
                                    @endphp
                                    {{ get_num_format($with_price) }}<br>
                                @endforeach
                                @php
                                    $total_with_users[$user->id] = $user_total;
                                @endphp
                            @endif
                        </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($restusers as $user)
                        <td style="padding: 15px"></td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($restusers as $user)
                        <td>
                            @if (in_array($user->id, $vacationsWorkerIds))
                                <span style="background-color: yellow">OFF</span>
                                @continue
                            @endif
                            @if (isset($total_with_users[$user->id]))
                                <strong>{{ get_currency() . get_num_format($total_with_users[$user->id]) }}</strong>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @if (!empty($payments_type))
                    @foreach ($payments_type as $index => $value)
                        @php
                            $total_with_users = [];
                        @endphp
                        @if (isset($payments_with_prices[$index]) && !empty($payments_with_prices[$index]))
                            <tr>
                                <td colspan="{{ count($restusers) }}">
                                    <strong>{{ $value }}</strong>
                                </td>
                            </tr>
                            <tr>
                                @foreach ($restusers as $user)
                                    @php
                                        $user_total = 0;
                                    @endphp
                                    <td>
                                        @if (in_array($user->id, $vacationsWorkerIds))
                                            <span style="background-color: yellow">OFF</span>
                                            @continue
                                        @endif
                                        @if (isset($payments_with_prices[$index][$user->id]))
                                            @foreach ($payments_with_prices[$index][$user->id] as $with_price)
                                                @php
                                                    $productPaymentMethods = get_payment_method_names('product');
                                                    $isProductPayment = in_array($index, $productPaymentMethods);
                                                @endphp
                                                @if ($isProductPayment)
                                                    @php
                                                        $user_total += $with_price['amount'];
                                                        if (isset($total_payments_types[$index])) {
                                                            $total_payments_types[$index] += $with_price['amount'];
                                                        } else {
                                                            $total_payments_types[$index] = $with_price['amount'];
                                                        }
                                                        $products = $with_price['products'];
                                                    @endphp
                                                    @foreach ($products as $product)
                                                        <div>
                                                            {{ $product->product->name }}
                                                            <br>
                                                            {{ $product->product->price }}
                                                        </div>
                                                        @if (!$loop->last)
                                                            <hr style="margin-top:5px;margin-bottom:5px;">
                                                        @endif
                                                    @endforeach
                                                    <br>
                                                @else
                                                    @php
                                                        $with_price_sum = is_array($with_price)
                                                            ? $with_price['amount']
                                                            : $with_price;
                                                        $user_total += $with_price_sum;
                                                    @endphp
                                                    @if (is_array($with_price))
                                                        <div style="color: {{ get_color_type($with_price['type']) }};border-bottom: 1px solid">
                                                            @if (optional($with_price)['details'])
                                                                @foreach ($with_price['details'] as $key => $amountItem)
                                                                    <span>
                                                                        {{ $amountItem }}
                                                                        {{ get_symbol_type($with_price['type']) }}
                                                                        @if (!empty($with_price['codesArr'][$key]))
                                                                            <br>{{ $with_price['codesArr'][$key] }}
                                                                        @endif
                                                                        <br>
                                                                        {{ $with_price['detailsArr'][$key]->full_name }}
                                                                        <br>
                                                                        {{ $with_price['detailsArr'][$key]->mobile }}
                                                                    </span>
                                                                    @if (!$loop->last)
                                                                        <hr style="margin-top:5px;margin-bottom:5px;">
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                {{ $with_price['amount'] }}
                                                                {{ get_symbol_type($with_price['type']) }}
                                                                @if (!empty($with_price['code']))
                                                                    <br> {{ $with_price['code'] }}
                                                                @endif
                                                                <br>{{ $with_price['client_name'] }}
                                                            @endif
                                                        </div>
                                                    @else
                                                        {{ get_num_format($with_price) }}
                                                    @endif
                                                    <br>
                                                    @php
                                                        if (isset($total_payments_types[$index])) {
                                                            $total_payments_types[$index] += $with_price_sum;
                                                        } else {
                                                            $total_payments_types[$index] = $with_price_sum;
                                                        }
                                                    @endphp
                                                @endif
                                            @endforeach
                                            @php
                                                $total_with_users[$user->id] = $user_total;
                                            @endphp
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($restusers as $user)
                                    <td>
                                        @if (in_array($user->id, $vacationsWorkerIds))
                                            <span style="background-color: yellow">OFF</span>
                                            @continue
                                        @endif
                                        @if (isset($total_with_users[$user->id]))
                                            <strong>{{ get_currency() . get_num_format($total_with_users[$user->id]) }}</strong>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                @endif
                <tr>
                    <td colspan="{{ count($restusers) }}">
                        <strong>{{ __('field.commission') }}</strong>
                    </td>
                </tr>
                @if (!empty($users_with_commission))
                    <tr>
                        @foreach ($restusers as $restuser)
                            @if (in_array($restuser->id, array_keys($users_with_commission)))
                                <td>
                                    @if (!empty($users_with_commission[$restuser->id]))
                                        @foreach ($users_with_commission[$restuser->id] as $commission_value)
                                            @php
                                                $total_commission += $commission_value;
                                            @endphp
                                            {{ get_num_format($commission_value) }}<br>
                                        @endforeach
                                    @else
                                        0.00
                                    @endif
                                </td>
                            @else
                                <td> </td>
                            @endif
                        @endforeach
                    </tr>
                @endif
                <tr>
                    <td colspan="{{ count($restusers) }}">
                        <strong>{{ __('field.tip') }}</strong>
                    </td>
                </tr>
                @if (!empty($users_with_tips))
                    <tr>
                        @foreach ($restusers as $restuser)
                            @if (in_array($restuser->id, array_keys($users_with_tips)))
                                <td>
                                    @if (!empty($users_with_tips[$restuser->id]))
                                        @foreach ($users_with_tips[$restuser->id] as $tips_value)
                                            @php
                                                $total_tips += $tips_value;
                                            @endphp
                                            {{ get_num_format($tips_value) }}<br>
                                        @endforeach
                                    @else
                                        0.00
                                    @endif
                                </td>
                            @else
                                <td> </td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    <br>
    @php
        $getPaymentsTypes = get_payment_types();
        $last_total = 0;
    @endphp

    <table class="table table-bordered">
        <tbody>
            <tr>
                <td colspan="{{ count($users) }}">
                    <h3 style="margin-bottom: 0;margin-top: 0">
                        <strong>{{ __('field.total') }}</strong>
                    </h3>
                </td>
            </tr>
            <tr>
                @if (!empty($getPaymentsTypes))
                    @foreach ($getPaymentsTypes as $getPaymentsType => $type)
                        @php
                            if (!isset($total_payments_types[$getPaymentsType])) {
                                continue;
                            }
                        @endphp
                        @php
                            if ($getPaymentsType != 'free' && $getPaymentsType != 'wallet') {
                                $last_total += $total_payments_types[$getPaymentsType];
                            }
                        @endphp
                        <td style="width: 50px;background-color: #666;color: #fff;text-align: center">
                            <strong>{{ $type }}</strong>
                        </td>
                        <td style="width:50px;">
                            <strong>{{ get_currency() . get_num_format($total_payments_types[$getPaymentsType]) }}</strong>
                        </td>
                    @endforeach
                @endif
                @foreach ($product_details_prices as $index => $product_details_price)
                    @if ($product_details_price != 0 && !optional($total_payments_types)[$index])
                        @php
                            $last_total += $product_details_price;
                        @endphp
                        <td style="width: 50px;background-color: #666;color: #fff;text-align: center">
                            <strong>{{ __('s.' . $index) }}</strong>
                        </td>
                        <td style="width:150px;">
                            <strong>{{ get_currency() . get_num_format($product_details_price) }}</strong>
                        </td>
                    @endif
                @endforeach
                @foreach ($wallet_details_prices as $index => $wallet_details_price)
                    @if ($wallet_details_price != 0)
                        @php
                            $last_total += $wallet_details_price;
                        @endphp
                        <td style="width: 50px;background-color: #666;color: #fff;text-align: center">
                            <strong>{{ $index }}</strong>
                        </td>
                        <td style="width:50px;">
                            <strong>{{ get_currency() . get_num_format($wallet_details_price) }}</strong>
                        </td>
                    @endif
                @endforeach
                <td style="width: 50px;background-color: #666;color: #fff;text-align: center">
                    <strong>
                        {{ __('field.commission') }}
                    </strong>
                </td>
                <td style="width:50px;">
                    <strong>{{ get_currency() . get_num_format($total_commission) }}</strong>
                </td>
                <td style="width: 50px;background-color: #666;color: #fff;text-align: center">
                    <strong>
                        {{ __('field.tip') }}
                    </strong>
                </td>
                <td style="width:50px;">
                    <strong>{{ get_currency() . get_num_format($total_tips) }}</strong>
                </td>
            </tr>
            <tr style="background-color: #666666;color: #ffffff">
                <td style="width: 50px;background-color: #666;color: #fff;text-align: center">
                    <strong>{{ __('field.total') }}</strong>
                </td>
                <td style="width:50px;color: #ffffff" colspan="{{ count($users) }}">
                    <strong>{{ get_currency() . get_num_format($last_total) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
</div>
