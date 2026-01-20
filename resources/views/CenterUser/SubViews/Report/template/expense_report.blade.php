<div class="table-responsive">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card" style="background-color: #374151; border-color: #4b5563;">
                <div class="card-body text-white">
                    <h5 class="card-title">{{ __('field.total_expenses') }}</h5>
                    <h3 class="card-text">{{ get_currency() }}{{ get_num_format($totalExpenses) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background-color: #374151; border-color: #4b5563;">
                <div class="card-body text-white">
                    <h5 class="card-title">{{ __('field.daily_expense_rate') }}</h5>
                    <h3 class="card-text">{{ get_currency() }}{{ get_num_format($dailyExpenseRate) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background-color: #374151; border-color: #4b5563;">
                <div class="card-body text-white">
                    <h5 class="card-title">{{ __('field.total_income') }}</h5>
                    <h3 class="card-text">{{ get_currency() }}{{ get_num_format($totalIncome) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card" style="background-color: #374151; border-color: #4b5563;">
                <div class="card-body text-white">
                    <h5 class="card-title">{{ __('field.net_profit') }}</h5>
                    <h3 class="card-text">{{ get_currency() }}{{ get_num_format($netProfit) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Information -->
    <div class="row mb-3 ">
        <div class="col-md-12">
            <div class="alert alert-dark">
                <h5><strong>{{ __('field.report_period') }}:</strong> {{ $start_date }} {{ __('field.to') }} {{ $end_date }}</h5>
                <p><strong>{{ __('field.total_days') }}:</strong> {{ $totalDays }} {{ __('field.days') }}</p>
                <p><strong>{{ __('field.average_daily_expense') }}:</strong> {{ get_currency() }}{{ get_num_format($dailyExpenseRate) }}</p>
            </div>
        </div>
    </div>

    <!-- Expenses Details Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>{{ __('field.expense_name') }}</th>
                <th>{{ __('field.payee') }}</th>
                <th>{{ __('field.amount') }}</th>
                <th>{{ __('field.start_date') }}</th>
                <th>{{ __('field.end_date') }}</th>
                <th>{{ __('field.duration') }}</th>
                <th>{{ __('field.daily_rate') }}</th>
                <th>{{ __('field.branch') }}</th>
                <!-- <th>{{ __('field.supplier') }}</th> -->
                <th>{{ __('field.notes') }}</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($expenses))
                @foreach ($expenses as $expense)
                    @php
                        $expenseStartDate = \Carbon\Carbon::parse($expense->start_date);
                        $expenseEndDate = \Carbon\Carbon::parse($expense->end_date);
                        $expenseDuration = $expenseStartDate->diffInDays($expenseEndDate) + 1;
                        $expenseDailyRate = $expenseDuration > 0 ? $expense->amount / $expenseDuration : 0;
                        
                        // Calculate overlap with report period
                        $reportStartDate = \Carbon\Carbon::parse($start_date);
                        $reportEndDate = \Carbon\Carbon::parse($end_date);
                        $overlapStart = $expenseStartDate->max($reportStartDate);
                        $overlapEnd = $expenseEndDate->min($reportEndDate);
                        $overlapDays = $overlapStart->diffInDays($overlapEnd) + 1;
                        $proportionalAmount = $expenseDailyRate * $overlapDays;
                    @endphp
                    <tr>
                        <td>{{ $expense->expense_name }}</td>
                        <td>{{ $expense->payee }}</td>
                        <td class="text-right">{{ get_currency() }}{{ get_num_format($expense->amount) }}</td>
                        <td>{{ $expense->start_date }}</td>
                        <td>{{ $expense->end_date }}</td>
                        <td class="text-center">{{ $expenseDuration }} {{ __('field.days') }}</td>
                        <td class="text-right">{{ get_currency() }}{{ get_num_format($expenseDailyRate) }}</td>
                        <td>{{ $expense->branch->name ?? '-' }}</td>
                        <!-- <td>{{ $expense->supplier->name ?? '-' }}</td> -->
                        <td>{{ $expense->notes ?? '-' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="text-center">{{ __('field.no_expenses_found') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Income Breakdown -->
    @if (!empty($incomeData['income_by_type']))
        <br>
        <h4>{{ __('field.income_breakdown') }}</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ __('field.payment_type') }}</th>
                    <th>{{ __('field.amount') }}</th>
                    <th>{{ __('field.percentage') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($incomeData['income_by_type'] as $type => $amount)
                    @php
                        $percentage = $totalIncome > 0 ? ($amount / $totalIncome) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                        <td class="text-right">{{ get_currency() }}{{ get_num_format($amount) }}</td>
                        <td class="text-right">{{ get_num_format($percentage, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <th>{{ __('field.total') }}</th>
                    <th class="text-right">{{ get_currency() }}{{ get_num_format($totalIncome) }}</th>
                    <th class="text-right">100%</th>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Daily Income vs Expenses -->
    @if (!empty($incomeData['income_by_date']))
        <br>
        <h4>{{ __('field.daily_income_vs_expenses') }}</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>{{ __('field.date') }}</th>
                    <th>{{ __('field.income') }}</th>
                    <th>{{ __('field.daily_expense') }}</th>
                    <th>{{ __('field.net_daily') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $currentDate = \Carbon\Carbon::parse($start_date);
                    $endDate = \Carbon\Carbon::parse($end_date);
                @endphp
                @while ($currentDate->lte($endDate))
                    @php
                        $dateStr = $currentDate->format('Y-m-d');
                        $dailyIncome = $incomeData['income_by_date'][$dateStr] ?? 0;
                        $dailyExpense = $dailyExpenseRate;
                        $netDaily = $dailyIncome - $dailyExpense;
                    @endphp
                    <tr>
                        <td>{{ $currentDate->format('d/m/Y') }}</td>
                        <td class="text-right">{{ get_currency() }}{{ get_num_format($dailyIncome) }}</td>
                        <td class="text-right">{{ get_currency() }}{{ get_num_format($dailyExpense) }}</td>
                        <td class="text-right {{ $netDaily >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ get_currency() }}{{ get_num_format($netDaily) }}
                        </td>
                    </tr>
                    @php
                        $currentDate->addDay();
                    @endphp
                @endwhile
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <th>{{ __('field.total') }}</th>
                    <th class="text-right">{{ get_currency() }}{{ get_num_format($totalIncome) }}</th>
                    <th class="text-right">{{ get_currency() }}{{ get_num_format($totalExpenses) }}</th>
                    <th class="text-right {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ get_currency() }}{{ get_num_format($netProfit) }}
                    </th>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Summary Statistics -->
    <br>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('field.expense_statistics') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('field.total_expenses') }}:</strong> {{ get_currency() }}{{ get_num_format($totalExpenses) }}</p>
                    <p><strong>{{ __('field.average_daily_expense') }}:</strong> {{ get_currency() }}{{ get_num_format($dailyExpenseRate) }}</p>
                    <p><strong>{{ __('field.total_expense_days') }}:</strong> {{ $totalDays }} {{ __('field.days') }}</p>
                    <p><strong>{{ __('field.number_of_expenses') }}:</strong> {{ count($expenses) }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('field.profitability_analysis') }}</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ __('field.total_income') }}:</strong> {{ get_currency() }}{{ get_num_format($totalIncome) }}</p>
                    <p><strong>{{ __('field.total_expenses') }}:</strong> {{ get_currency() }}{{ get_num_format($totalExpenses) }}</p>
                    <p><strong>{{ __('field.net_profit') }}:</strong> 
                        <span class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ get_currency() }}{{ get_num_format($netProfit) }}
                        </span>
                    </p>
                    <p><strong>{{ __('field.profit_margin') }}:</strong> 
                        @php
                            $profitMargin = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;
                        @endphp
                        <span class="{{ $profitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ get_num_format($profitMargin, 1) }}%
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

