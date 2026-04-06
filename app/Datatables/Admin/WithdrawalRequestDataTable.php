<?php

namespace App\Datatables\Admin;

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
            ->editColumn('center_id', function ($row) {
                return $row->center ? $row->center->name : 'Unknown';
            })
            ->editColumn('phone', function ($row) {
                return $row->center ? $row->center->country_code . ' ' . $row->center->phone : '---';
            })
            ->editColumn('email', function ($row) {
                return $row->center ? $row->center->email : '---';
            })
            ->editColumn('bank_name', function ($row) {
                return $row->center ? $row->center->bank_name : '---';
            })
            ->editColumn('wallet', function ($row) {
                return $row->center ? number_format($row->center->wallet, 2) : '---';
            })
            ->editColumn('amount', function ($row) {
                return number_format($row->amount, 2);
            })
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
            ->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at->format('Y-m-d H:i') : '---';
            })
            ->editColumn('action', function ($row) {
                if ($row->status === 'pending') {
                    $approveRoute = route("admin.withdrawal_requests.approve", $row->id);
                    $html = '<button onclick="approveRequest(\'' . $approveRoute . '\')" class="btn btn-sm btn-success me-1" title="Approve"><i class="ti ti-check"></i></button>';
                    $html .= '<button onclick="rejectRequest(' . $row->id . ')" class="btn btn-sm btn-danger" title="Reject"><i class="ti ti-x"></i></button>';
                    return $html;
                }
                return '---';
            })
            ->rawColumns(['status', 'action'])
            ->setRowId('id');
    }

    public function query(CenterWithdrawalRequest $model): QueryBuilder
    {
        return $model->newQuery()->with('center')->orderBy('status', 'desc')->orderBy('id', 'desc'); // Pending first usually due to alphabetical order 'pending' > 'confirmed' but we can just order by ID desc for now. Let's just order by ID desc.
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
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
            Column::make('center_id')->searchable(true)->title(__('field.center') ?? 'Center'),
            Column::make('phone')->searchable(false)->title(__('field.phone') ?? 'Phone'),
            Column::make('email')->searchable(false)->title(__('field.email') ?? 'Email'),
            Column::make('bank_name')->searchable(false)->title(__('field.bank_name') ?? 'Bank Name'),
            Column::make('wallet')->searchable(false)->title(__('field.wallet_balance') ?? 'Wallet'),
            Column::make('amount')->searchable(true)->title(__('field.amount') ?? 'Amount'),
            Column::make('status')->searchable(true)->title(__('field.status') ?? 'Status'),
            Column::make('created_at')->searchable(true)->title(__('field.date') ?? 'Date'),
            Column::make('updated_at')->searchable(true)->title(__('field.updated_at') ?? 'Updated At'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
