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
                $user = auth('center_user')->user();
                $canUpdate = $user?->can('UPDATE_STOCKTAKES', 'center_api');
                $canDelete = $user?->can('DELETE_STOCKTAKES', 'center_api');
                $options = [
                    'show' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];

                if ($canDelete) {
                    $options['delete'] = true;
                }

                if ($canUpdate && $item->status === 'ordered') {
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
            ->addColumn('source_type_label', function ($row) {
                return $row->source_type === 'main_stock'
                    ? e(__('field.main_stock'))
                    : e(__('locale.suppliers'));
            })
            ->addColumn('order_items', function ($row) {
                if ($row->items->isEmpty()) {
                    return '—';
                }

                $hideCost = $row->source_type === 'main_stock';
                $html = '<div class="lz-order-items">';
                foreach ($row->items as $item) {
                    $sku = $item->product?->primarySku?->sku;
                    $received = $item->received_qty !== null
                        ? number_format((float) $item->received_qty, 2)
                        : '—';
                    $html .= '<div class="lz-order-item">';
                    $html .= '<div class="fw-semibold">' . e($item->product->name ?? '-') . '</div>';
                    if ($sku) {
                        $html .= '<small class="text-muted">SKU: ' . e($sku) . '</small>';
                    }
                    $html .= '<div class="small text-muted">';
                    $html .= e(__('field.ordered')) . ': ' . number_format((float) $item->order_qty, 2);
                    $html .= ' · ' . e(__('field.received')) . ': ' . $received;
                    if (!$hideCost) {
                        $html .= '<br>' . e(__('field.unit_cost')) . ': ' . number_format((float) $item->unit_cost, 2) . ' ' . e(get_currency());
                        $html .= ' · ' . e(__('field.total_cost')) . ': ' . number_format((float) $item->line_total, 2) . ' ' . e(get_currency());
                    }
                    $html .= '</div></div>';
                }
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['action', 'status', 'order_number', 'total_cost', 'order_items'])
            ->setRowId('id');
    }

    public function query(StockOrder $model): QueryBuilder
    {
        $query = $model->query()
            ->withTrashed()
            ->with(['supplier', 'branches.translation', 'items.product.translation', 'items.product.primarySku'])
            ->orderBy('stock_orders.id', 'DESC');

        if ((int) get_user_role() !== 1) {
            $branchId = auth('center_user')->user()?->branch_id;
            if ($branchId) {
                $query->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId));
            }
        }

        return $query;
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
            Column::computed('source_type_label')->searchable(false)->title(__('field.stock_order_source'))->visible(false),
            Column::computed('order_items')->searchable(false)->orderable(false)->title(__('locale.products'))->visible(false),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
