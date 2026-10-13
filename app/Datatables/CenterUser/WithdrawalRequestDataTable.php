<?php

namespace App\Datatables\CenterUser;

use App\Models\CenterWithdrawalRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WithdrawalRequestDataTable extends DataTable
{
    private $plural = 'withdrawal_requests';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('amount', function ($row) {
                return '<span class="lz-amount">' . number_format((float) $row->amount, 2) . '</span>';
            })
            ->editColumn('status', function ($row) {
                $class = match ($row->status) {
                    'confirmed' => 'is-confirmed',
                    'rejected' => 'is-rejected',
                    default => 'is-pending',
                };
                return '<span class="lz-status-badge ' . $class . '">' . e(ucfirst($row->status)) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '—';
            })
            ->editColumn('admin_notes', function ($row) {
                return $row->admin_notes ? \App\Helpers\MyHelper::truncateWithReadMore($row->admin_notes) : '—';
            })
            ->rawColumns(['amount', 'status', 'admin_notes'])
            ->setRowId('id');
    }

    public function query(CenterWithdrawalRequest $model): QueryBuilder
    {
        $currentDb = config('database.connections.mysql.database');
        $center = \App\Models\Center::where('database', $currentDb)->first();

        if ($center) {
            return $model->newQuery()->where('center_id', $center->id)->orderBy('id', 'desc');
        }

        return $model->newQuery()->where('id', -1);
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
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
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('dtr-control all'),
            Column::make('amount')->searchable(true)->title(__('field.amount') ?? 'Amount')->responsivePriority(1)->addClass('all'),
            Column::make('status')->searchable(true)->title(__('field.status') ?? 'Status')->responsivePriority(1)->addClass('all'),
            Column::make('created_at')->searchable(true)->title(__('field.date') ?? 'Date')->responsivePriority(2)->addClass('all'),
            Column::make('admin_notes')->searchable(true)->title(__('field.admin_notes') ?? 'Admin Notes')->responsivePriority(3)->addClass('all'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
