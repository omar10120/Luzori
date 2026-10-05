<?php

namespace App\Datatables\CenterUser;

use App\Models\MainStock;
use App\Services\MainStockService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
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
            ->addColumn('product_name', fn (MainStock $stock) => e($stock->product?->name ?? '-'))
            ->addColumn('sku', fn (MainStock $stock) => e($stock->sku?->sku ?? '-'))
            ->addColumn('available_quantity', fn (MainStock $stock) => $stock->available_quantity)
            ->editColumn('quantity', fn (MainStock $stock) => (int) $stock->quantity)
            ->editColumn('reserved_quantity', fn (MainStock $stock) => (int) $stock->reserved_quantity)
            ->editColumn('reorder_level', fn (MainStock $stock) => (int) $stock->reorder_level)
            ->setRowId('id');
    }

    public function query(MainStock $model): QueryBuilder
    {
        return $this->mainStockService->query();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('mainstocks-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->responsive(true)
            ->addTableClass('table table-bordered table-hover')
            ->language(app()->getLocale() === 'ar'
                ? 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/ar.json'
                : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json');
    }

    public function getColumns(): array
    {
        return [
            Column::computed('product_name')->title(__('field.product_name'))->searchable(false)->orderable(false),
            Column::computed('sku')->title(__('field.sku'))->searchable(false)->orderable(false),
            Column::make('quantity')->title(__('field.quantity')),
            Column::make('reserved_quantity')->title(__('field.reserved_quantity')),
            Column::computed('available_quantity')->title(__('field.available_quantity'))->searchable(false)->orderable(false),
            // Column::make('reorder_level')->title(__('field.reorder_level')),
        ];
    }

    protected function filename(): string
    {
        return 'main_stocks_' . date('YmdHis');
    }
}
