@php
    $fontFamily = App::getLocale() == 'ar' ? 'Cairo' : 'Poppins';
@endphp
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">
<style>
    .lz-expense-report {
        font-family: '{{ $fontFamily }}', sans-serif !important;
    }
    .lz-expense-report .lz-stat-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin-bottom: 1.15rem;
    }
    .lz-expense-report .lz-stat-card {
        flex: 1 1 180px;
        background: #fff;
        border: 1px solid #efe6d8;
        border-radius: 14px;
        padding: 1rem 1.1rem;
        box-shadow: 0 4px 14px rgba(0, 43, 38, 0.04);
        position: relative;
        overflow: hidden;
    }
    .lz-expense-report .lz-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        inset-inline-start: 0;
        width: 4px;
        height: 100%;
        background: #002b26;
    }
    .lz-expense-report .lz-stat-card.is-gold::before { background: #c5a070; }
    .lz-expense-report .lz-stat-card.is-mint::before { background: #1a6b5a; }
    .lz-expense-report .lz-stat-card.is-cream::before { background: #8a6a3d; }
    .lz-expense-report .lz-stat-label {
        display: block;
        color: #7a8582;
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .lz-expense-report .lz-stat-value {
        display: block;
        color: #002b26;
        font-size: 1.35rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.25;
    }
    .lz-expense-report .lz-period-box {
        background: #f7f3ec;
        border: 1px solid #efe6d8;
        border-radius: 14px;
        padding: 1rem 1.15rem;
        margin-bottom: 1.25rem;
        color: #1a2b28;
    }
    .lz-expense-report .lz-period-box h5 {
        color: #002b26;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 0.45rem;
    }
    .lz-expense-report .lz-period-box p {
        margin-bottom: 0.25rem;
        color: #4a5552;
        font-size: 0.9rem;
    }
    .lz-expense-report .lz-period-box p:last-child {
        margin-bottom: 0;
    }
    .lz-expense-report .lz-section-title {
        color: #002b26;
        font-weight: 700;
        font-size: 1.05rem;
        margin: 1.35rem 0 0.75rem;
    }
    .lz-expense-report .table {
        font-size: 0.88rem;
        margin-bottom: 0;
    }
    .lz-expense-report .table thead th {
        background: #f7f3ec !important;
        color: #002b26 !important;
        font-weight: 700 !important;
        border-color: #efe6d8 !important;
        white-space: nowrap;
    }
    .lz-expense-report .table td,
    .lz-expense-report .table th {
        border-color: #efe6d8 !important;
        vertical-align: middle;
    }
    .lz-expense-report .table-striped > tbody > tr:nth-of-type(odd) > * {
        background: #fbfaf7;
    }
    .lz-expense-report .lz-side-card {
        border: 1px solid #efe6d8 !important;
        border-radius: 14px !important;
        box-shadow: none !important;
        overflow: hidden;
        height: 100%;
        background: #fff !important;
    }
    .lz-expense-report .lz-side-card .card-header {
        background: #f7f3ec !important;
        border-bottom: 1px solid #efe6d8 !important;
        padding: 0.85rem 1.1rem !important;
    }
    .lz-expense-report .lz-side-card .card-header h5 {
        margin: 0;
        color: #002b26;
        font-weight: 700;
        font-size: 0.95rem;
    }
    .lz-expense-report .lz-side-card .card-body {
        padding: 1rem 1.1rem !important;
    }
    .lz-expense-report .lz-side-card .card-body p {
        margin-bottom: 0.55rem;
        color: #1a2b28;
        font-size: 0.9rem;
    }
    .lz-expense-report .lz-side-card .card-body p:last-child {
        margin-bottom: 0;
    }
    .lz-expense-report .table-responsive {
        font-size: 12px;
        overflow-x: auto;
    }
</style>

<div class="lz-expense-report table-responsive">
    <!-- Summary Cards -->
    <div class="lz-stat-row">
        <div class="lz-stat-card">
            <span class="lz-stat-label">{{ __('field.total_expenses') }}</span>
            <span class="lz-stat-value">{{ get_currency() }}{{ get_num_format($totalExpenses) }}</span>
        </div>
        <div class="lz-stat-card is-gold">
            <span class="lz-stat-label">{{ __('field.daily_expense_rate') }}</span>
            <span class="lz-stat-value">{{ get_currency() }}{{ get_num_format($dailyExpenseRate) }}</span>
        </div>
        <div class="lz-stat-card is-mint">
            <span class="lz-stat-label">{{ __('field.total_income') }}</span>
            <span class="lz-stat-value">{{ get_currency() }}{{ get_num_format($totalIncome) }}</span>
        </div>
        <div class="lz-stat-card is-cream">
            <span class="lz-stat-label">{{ __('field.net_profit') }}</span>
            <span class="lz-stat-value">{{ get_currency() }}{{ get_num_format($netProfit) }}</span>
        </div>
    </div>

    <!-- Period Information -->
    <div class="lz-period-box">
        <h5><strong>{{ __('field.report_period') }}:</strong> {{ $start_date }} {{ __('field.to') }} {{ $end_date }}</h5>
        <p><strong>{{ __('field.total_days') }}:</strong> {{ $totalDays }} {{ __('field.days') }}</p>
        <p><strong>{{ __('field.average_daily_expense') }}:</strong> {{ get_currency() }}{{ get_num_format($dailyExpenseRate) }}</p>
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
        <h4 class="lz-section-title">{{ __('field.income_breakdown') }}</h4>
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
        <h4 class="lz-section-title">{{ __('field.daily_income_vs_expenses') }}</h4>
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
    <div class="row mt-4 g-3">
        <div class="col-md-6">
            <div class="card lz-side-card">
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
            <div class="card lz-side-card">
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
