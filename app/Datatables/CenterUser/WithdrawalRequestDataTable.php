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
            ->editColumn('status', function ($row) {
                $map = [
                    'pending'   => 'bg-label-warning',
                    'confirmed' => 'bg-label-success',
                    'rejected'  => 'bg-label-danger',
                ];
                $class = $map[$row->status] ?? 'bg-label-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '---';
            })
            ->editColumn('admin_notes', function ($row) {
                return $row->admin_notes ? \App\Helpers\MyHelper::truncateWithReadMore($row->admin_notes) : '---';
            })
            ->rawColumns(['status', 'admin_notes'])
            ->setRowId('id');
    }

    public function query(CenterWithdrawalRequest $model): QueryBuilder
    {
        // Get current center using current tenant database
        $currentDb = config('database.connections.mysql.database');
        $center = \App\Models\Center::where('database', $currentDb)->first();

        if ($center) {
            return $model->newQuery()->where('center_id', $center->id)->orderBy('id', 'desc');
        }

        return $model->newQuery()->where('id', -1); // Return empty if center not found
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->responsive(true)
            ->dom('
                <"card-header border-bottom p-3 d-flex justify-content-between align-items-center"
                    <"head-label"><"dt-action-buttons"B>
                >
                <"d-flex justify-content-between align-items-center mx-0 row"
                    <"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"fr>
                >
                <"table-responsive"t>
                <"d-flex justify-content-between mx-0 row"
                    <"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>
                >
            ')
            ->selectStyleSingle()
            ->addAction(['printable' => false, 'exportable' => false, 'className' => 'dt-center'])
            ->buttons([
                Button::make('colvis')->addClass($buttonClass . ' btn-warning')->text(__('general.column_visibility')),
                [
                    'extend' => 'collection',
                    'text' => __('general.export'),
                    'className' => $buttonClass,
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
            ->addTableClass('table table-bordered table-hover')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == "ar" ?
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/ar.json" :
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#'),
            Column::make('amount')->searchable(true)->title(__('field.amount') ?? 'Amount'),
            Column::make('status')->searchable(true)->title(__('field.status') ?? 'Status'),
            Column::make('created_at')->searchable(true)->title(__('field.date') ?? 'Date'),
            Column::make('admin_notes')->searchable(true)->title(__('field.admin_notes') ?? 'Admin Notes'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
