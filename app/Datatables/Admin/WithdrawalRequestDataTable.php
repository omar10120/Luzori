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
                $eye = '<a href="javascript:void(0)" class="lz-action-btn lz-open-admin-row-details lz-admin-view-details" title="' . e(__('general.view_details')) . '"'
                    . ' onclick="event.preventDefault();event.stopPropagation();if(window.lzOpenAdminRowDetails){window.lzOpenAdminRowDetails(this);}return false;">'
                    . '<i class="ti ti-eye"></i></a>';
                if ($row->status === 'pending') {
                    $approveRoute = route("admin.withdrawal_requests.approve", $row->id);
                    return '<div class="lz-action-icons" onclick="event.stopPropagation()">'
                        . $eye
                        . '<a href="javascript:void(0)" class="lz-action-btn" title="Approve" onclick="approveRequest(\'' . $approveRoute . '\')"><i class="ti ti-check"></i></a>'
                        . '<a href="javascript:void(0)" class="lz-action-btn lz-action-danger" title="Reject" onclick="rejectRequest(' . $row->id . ')"><i class="ti ti-x"></i></a>'
                        . '</div>';
                }
                return '<div class="lz-action-icons" onclick="event.stopPropagation()">' . $eye . '</div>';
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
            ->responsive(false)
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
            ->addAction(['printable' => false, 'exportable' => false, 'className' => 'dt-center lz-actions-col all noVis'])
            ->buttons([
                [
                    'extend' => 'colvis',
                    'className' => $buttonClass . ' btn-luzori-beige',
                    'text' => __('general.column_visibility'),
                    'columns' => ':not(.noVis)',
                ],
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
            ->addTableClass('table table-hover')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == "ar" ?
            asset('js/lang/ar_custom.json') :
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#')->addClass('all'),
            Column::make('center_id')->searchable(true)->title(__('field.center') ?? 'Center')->addClass('all'),
            Column::make('amount')->searchable(true)->title(__('field.amount') ?? 'Amount')->addClass('all'),
            Column::make('status')->searchable(true)->title(__('field.status') ?? 'Status')->addClass('all'),
            Column::make('created_at')->searchable(true)->title(__('field.date') ?? 'Date')->addClass('all'),
            Column::make('phone')->searchable(false)->title(__('field.phone') ?? 'Phone')->visible(false),
            Column::make('email')->searchable(false)->title(__('field.email') ?? 'Email')->visible(false),
            Column::make('bank_name')->searchable(false)->title(__('field.bank_name') ?? 'Bank Name')->visible(false),
            Column::make('wallet')->searchable(false)->title(__('field.wallet_balance') ?? 'Wallet')->visible(false),
            Column::make('updated_at')->searchable(true)->title(__('field.updated_at') ?? 'Updated At')->visible(false),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
