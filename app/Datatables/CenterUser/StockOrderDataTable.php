<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\StockOrder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockOrderDataTable extends DataTable
{
    private $model = 'StockOrder';
    private $plural = 'stockorders';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'show' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];

                if ($item->status === 'ordered') {
                    $options['receive_url'] = route('center_user.stockorders.receive', ['id' => $id]);
                }

                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->editColumn('order_number', function ($row) {
                return '<span class="lz-order-num">' . e($row->order_number) . '</span>';
            })
            ->editColumn('expected_at', function ($row) {
                if (!$row->expected_at) {
                    return '—';
                }
                return $row->expected_at instanceof \Carbon\Carbon
                    ? $row->expected_at->format('Y-m-d')
                    : \Carbon\Carbon::parse($row->expected_at)->format('Y-m-d');
            })
            ->editColumn('deliver_from', function ($row) {
                return e($row->deliver_from ?: ($row->supplier->name ?? '—'));
            })
            ->editColumn('branch_name', function ($row) {
                $names = $row->branches->map(fn ($b) => $b->name)->filter()->values();
                return $names->isEmpty() ? '—' : e($names->implode(', '));
            })
            ->editColumn('total_cost', function ($row) {
                return '<span class="lz-cost">' . number_format((float) $row->total_cost, 2) . ' ' . e(get_currency()) . '</span>';
            })
            ->editColumn('status', function ($row) {
                $class = $row->status === 'received' ? 'is-received' : 'is-ordered';
                $label = $row->status === 'received'
                    ? __('field.received')
                    : __('field.ordered');
                return '<span class="lz-status-badge ' . $class . '">' . e($label) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ?: '—';
            })
            ->rawColumns(['action', 'status', 'order_number', 'total_cost'])
            ->setRowId('id');
    }

    public function query(StockOrder $model): QueryBuilder
    {
        return $model->query()
            ->withTrashed()
            ->with(['supplier', 'branches.translation'])
            ->orderBy('stock_orders.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->responsive(false)
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
                'className' => 'dt-center lz-actions-col all noVis',
                'title' => __('general.actions'),
            ])
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
            Column::make('order_number')->searchable(true)->title(__('field.order_number'))->responsivePriority(1)->addClass('dtr-control all'),
            Column::make('created_at')->searchable(true)->title(__('field.created'))->responsivePriority(2)->addClass('all'),
            Column::make('expected_at')->searchable(false)->title(__('field.expected'))->responsivePriority(3)->addClass('all'),
            Column::computed('branch_name')->searchable(false)->title(__('field.branch'))->responsivePriority(2)->addClass('all'),
            Column::make('deliver_from')->searchable(true)->title(__('field.deliver_from'))->responsivePriority(3)->addClass('all'),
            Column::make('total_cost')->searchable(false)->title(__('field.total_cost'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->responsivePriority(1)->addClass('all'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
