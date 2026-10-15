<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ExpensesProviderDataTable extends DataTable
{
    private $model = 'ExpensesProvider';
    private $plural = 'expenses';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'edit' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->editColumn('expense_name', function ($row) {
                return '<span class="lz-expense-type">' . e($row->expense_name ?: '—') . '</span>';
            })
            ->editColumn('payee', function ($row) {
                return e($row->payee ?: '—');
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('Y-m-d') : '—';
            })
            ->editColumn('end_date', function ($row) {
                return $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('Y-m-d') : '—';
            })
            ->editColumn('amount', function ($row) {
                return '<span class="lz-amount">' . number_format((float) $row->amount, 2) . ' ' . e(get_currency()) . '</span>';
            })
            ->editColumn('date', function ($row) {
                return $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : '—';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i') : '—';
            })
            ->editColumn('receipt_image', function ($row) {
                if ($row->receipt_image) {
                    return '<img src="' . e($row->receipt_image_url) . '" alt="Receipt" class="lz-receipt-thumb receipt-thumbnail" data-bs-toggle="modal" data-bs-target="#receiptModal" data-receipt-url="' . e($row->receipt_image_url) . '">';
                }
                return '<span class="text-muted">—</span>';
            })
            ->editColumn('branch.name', function ($row) {
                return e($row->branch?->name ?? '—');
            })
            ->editColumn('worker_description', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->worker_description ?? '');
            })
            ->editColumn('notes', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->notes ?? '');
            })
            ->rawColumns(['expense_name', 'amount', 'receipt_image', 'notes', 'action', 'worker_description'], true)
            ->setRowId('id');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        return $this->builder()
            ->setTableId('expenses_providers-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax('', null, [
                'branch_id' => 'function () { return $("#branchFilter").val(); }',
                'date' => 'function () { return $("#dateFilter").val(); }',
                'expense_name' => 'function () { return $("#nameFilter").val(); }',
            ])
            ->orderBy(0, 'desc')
            ->responsive([
                'details' => [
                    'type' => 'column',
                    'target' => 0,
                ],
            ])
            ->dom('
                <"card-header border-bottom p-3 d-flex justify-content-between align-items-center"
                    <"head-label"><"dt-action-buttons"B>
                >
                <"d-flex justify-content-between align-items-center mx-0 row px-1 pt-2"
                    <"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"fr>
                >
                <"table-responsive"t>
                <"d-flex justify-content-between mx-0 row px-1 pt-2"
                    <"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>
                >
            ')
            ->selectStyleSingle()
            ->addAction([
                'printable' => false,
                'exportable' => false,
                'responsivePriority' => 1,
                'className' => 'dt-center lz-actions-col',
                'title' => __('general.actions'),
            ])
            ->buttons([
                Button::make('colvis')->addClass($buttonClass . ' btn-luzori-beige')->text(__('general.column_visibility')),
                [
                    'extend' => 'collection',
                    'text' => __('general.export'),
                    'className' => $buttonClass . ' btn-luzori-muted',
                    'buttons' => [
                        'excel',
                        'csv',
                        'pdf',
                        'print',
                        'copy',
                    ],
                ],
            ])
            ->language($this->getDataTableLanguageUrl())
            ->addTableClass('table table-hover')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == 'ar'
            ? asset('js/lang/ar_custom.json')
            : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json';
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('all'),
            Column::make('expense_name')->searchable(true)->title(__('field.expense_name'))->responsivePriority(1)->addClass('all'),
            Column::make('payee')->searchable(true)->title(__('field.payee'))->responsivePriority(2)->addClass('all'),
            Column::make('date')->searchable(true)->title(__('field.date'))->responsivePriority(2)->addClass('all'),
            Column::make('branch.name')->searchable(false)->title(__('field.branch'))->responsivePriority(2)->addClass('all'),
            Column::make('start_date')->searchable(true)->title(__('field.start_date'))->addClass('none'),
            Column::make('end_date')->searchable(true)->title(__('field.end_date'))->addClass('none'),
            Column::computed('amount')->searchable(false)->title(__('field.amount'))->responsivePriority(1)->addClass('all'),
            Column::computed('receipt_image')->searchable(false)->title(__('field.receipt'))->responsivePriority(3)->addClass('all'),
            Column::computed('notes')->searchable(false)->title(__('field.notes'))->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    public function query(Expense $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['branch', 'supplier'])
            ->when(request('branch_id'), function (QueryBuilder $query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when(request('date'), function (QueryBuilder $query, $date) {
                $query->whereDate('date', $date);
            })
            ->when(request('expense_name'), function (QueryBuilder $query, $expenseName) {
                $query->where('expense_name', $expenseName);
            })
            ->orderBy('created_at', 'desc');
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
