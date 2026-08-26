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
                    'edit' => false,
                    'show' => false,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];

                $actions = '';
                if ($item->status === 'ordered') {
                    $actions .= '<a href="' . route('center_user.stockorders.receive', ['id' => $id]) . '" class="btn btn-sm btn-primary me-1" title="' . e(__('general.receive')) . '"><i class="ti ti-package-import"></i></a>';
                }
                $actions .= '<a href="' . route('center_user.stockorders.show', ['id' => $id]) . '" class="btn btn-sm btn-success me-1" title="' . e(__('general.show')) . '"><i class="ti ti-eye"></i></a>';

                $html = view()->make('_partials.center_actions', compact('id', 'route', 'options', 'model'))->render();
                return $actions . $html;
            })
            ->editColumn('order_number', function ($row) {
                return e($row->order_number);
            })
            ->editColumn('expected_at', function ($row) {
                return $row->expected_at ? $row->expected_at->format('Y-m-d') : '-';
            })
            ->editColumn('deliver_from', function ($row) {
                return e($row->deliver_from ?: ($row->supplier->name ?? '-'));
            })
            ->editColumn('total_cost', function ($row) {
                return number_format((float) $row->total_cost, 2) . ' ' . get_currency();
            })
            ->editColumn('status', function ($row) {
                $class = $row->status === 'received' ? 'bg-label-success' : 'bg-label-warning';
                $label = $row->status === 'received'
                    ? __('field.received')
                    : __('field.ordered');
                return '<span class="badge ' . $class . '">' . e($label) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-';
            })
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    public function query(StockOrder $model): QueryBuilder
    {
        return $model->query()
            ->withTrashed()
            ->with(['supplier'])
            ->orderBy('stock_orders.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.create');

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
            ->addAction(['printable' => false, 'exportable' => false, 'className' => 'dt-center', 'title' => __('general.actions')])
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
            ->initComplete('function () {
                $(".dt-action-buttons").append("<a href=' . $addRoute . ' class=\"btn btn-primary mx-2\">' . __('general.add_new') . ' <i class=\"ti ti-plus\"></i></a>");
            }')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == 'ar'
            ? 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/ar.json'
            : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json';
    }

    public function getColumns(): array
    {
        return [
            Column::make('order_number')->searchable(true)->title(__('field.order_number')),
            Column::make('created_at')->searchable(true)->title(__('field.created')),
            Column::make('expected_at')->searchable(false)->title(__('field.expected')),
            Column::make('deliver_from')->searchable(true)->title(__('field.deliver_from')),
            Column::make('total_cost')->searchable(false)->title(__('field.total_cost')),
            Column::computed('status')->searchable(false)->title(__('field.status')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
