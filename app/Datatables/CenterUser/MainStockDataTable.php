<?php

namespace App\Datatables\CenterUser;

use App\Models\MainStock;
use App\Services\MainStockService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MainStockDataTable extends DataTable
{
    public function __construct(private MainStockService $mainStockService)
    {
    }

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('product_name', fn (MainStock $stock) => e($stock->product?->name ?? '—'))
            ->addColumn('sku', fn (MainStock $stock) => e($stock->sku?->sku ?? '—'))
            ->addColumn('available_quantity', function (MainStock $stock) {
                return '<span class="lz-stock-ok">' . (int) $stock->available_quantity . '</span>';
            })
            ->editColumn('quantity', function (MainStock $stock) {
                return '<span class="lz-stock-num">' . (int) $stock->quantity . '</span>';
            })
            ->editColumn('reserved_quantity', function (MainStock $stock) {
                return '<span class="lz-stock-muted">' . (int) $stock->reserved_quantity . '</span>';
            })
            ->editColumn('reorder_level', function (MainStock $stock) {
                return '<span class="lz-stock-muted">' . (int) $stock->reorder_level . '</span>';
            })
            ->rawColumns(['quantity', 'reserved_quantity', 'available_quantity', 'reorder_level'], true)
            ->setRowId('id');
    }

    public function query(MainStock $model): QueryBuilder
    {
        return $this->mainStockService->query();
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        return $this->builder()
            ->setTableId('mainstocks-table')
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
            ->addTableClass('table table-hover')
            ->language(app()->getLocale() === 'ar'
                ? asset('js/lang/ar_custom.json')
                : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json');
    }

    public function getColumns(): array
    {
        return [
            Column::computed('product_name')
                ->title(__('field.product_name'))
                ->searchable(false)
                ->orderable(false)
                ->responsivePriority(1)
                ->addClass('dtr-control all'),
            Column::computed('sku')
                ->title(__('field.sku'))
                ->searchable(false)
                ->orderable(false)
                ->responsivePriority(1)
                ->addClass('all'),
            Column::make('quantity')
                ->title(__('field.quantity'))
                ->responsivePriority(2)
                ->addClass('all'),
            Column::make('reserved_quantity')
                ->title(__('field.reserved_quantity'))
                ->responsivePriority(2)
                ->addClass('all'),
            Column::computed('available_quantity')
                ->title(__('field.available_quantity'))
                ->searchable(false)
                ->orderable(false)
                ->responsivePriority(1)
                ->addClass('all'),
            Column::make('reorder_level')
                ->title(__('field.reorder_level'))
                ->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return 'main_stocks_' . date('YmdHis');
    }
}
