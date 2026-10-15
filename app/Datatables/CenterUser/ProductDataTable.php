<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
    private $model = 'Product';
    private $plural = 'products';

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
            ->editColumn('status', function ($row) {
                $checked = $row->deleted_at ? '' : 'checked';
                $operation = $row->deleted_at ? DeleteActionEnum::RESTORE_DELETED->value : DeleteActionEnum::SOFT_DELETE->value;
                return '<label class="switch switch-square" onclick="event.stopPropagation()">
                            <input onChange="changeStatus(\'' . $this->model . '\',\'' . $row->id . '\',\'' . $operation . '\')"
                                type="checkbox" class="switch-input"' . $checked . '>
                            <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->editColumn('image', function ($row) {
                return '<img src="' . e($row->image) . '" alt="">';
            })
            ->editColumn('skus', function ($row) {
                $skus = $row->skus;
                if ($skus && $skus->count() > 0) {
                    return '<div>' . e(implode(', ', $skus->pluck('sku')->toArray())) . '</div>';
                }
                return '—';
            })
            ->editColumn('productSuppliers', function ($row) {
                $suppliers = $row->productSuppliers;
                if ($suppliers && $suppliers->count() > 0) {
                    return e($suppliers->pluck('name')->join(', '));
                }
                return '—';
            })
            ->editColumn('qty', function ($row) {
                $branches = $row->productBranches;
                if (!$branches || $branches->isEmpty()) {
                    return '<span class="lz-qty-total">0</span>';
                }

                $total = (int) $branches->sum('stock_quantity');
                if ($branches->count() === 1) {
                    return '<span class="lz-qty-total">' . $total . '</span>';
                }

                $details = $branches->map(function ($pb) {
                    $name = $pb->branch->name ?? ('#' . $pb->branch_id);
                    return e($name) . ': ' . (int) $pb->stock_quantity;
                })->implode('<br>');

                return '<div><span class="lz-qty-total">' . $total . '</span><br><small class="text-muted">' . $details . '</small></div>';
            })
            ->editColumn('translation.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->translation->name ?? '');
            })
            ->editColumn('translation.text', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->translation->text ?? '', 100);
            })
            ->rawColumns(['action', 'image', 'status', 'skus', 'qty', 'translation.name', 'translation.text'], true)
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        return $model->query()
            ->withTrashed()
            ->with(['translation', 'brand', 'category', 'productSuppliers', 'skus', 'productBranches.branch.translation'])
            ->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.create');

        $initCompleteJs = 'function () {';
            $initCompleteJs .= 'var $lzAct=$(".dt-action-buttons").first();if($lzAct.length)$lzAct.append("<a href=\'' . $addRoute . '\' class=\"btn btn-luzori-primary btn-sm mx-1 px-3 py-2\">' . __('general.add_new') . ' <i class=\"ti ti-plus\"></i></a>");';
        $initCompleteJs .= '}';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
            ->initComplete($initCompleteJs)
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
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('dtr-control all'),
            Column::computed('translation.name')->searchable(true)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::computed('skus')->searchable(false)->title(__('field.sku'))->responsivePriority(2)->addClass('all'),
            Column::computed('brand.name')->searchable(true)->title(__('field.brand'))->responsivePriority(3)->addClass('all'),
            Column::computed('category.name')->searchable(true)->title(__('field.category'))->responsivePriority(3)->addClass('all'),
            Column::computed('qty')->searchable(false)->title(__('field.quantity'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->responsivePriority(2)->addClass('all'),
            // Expandable details
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
            Column::computed('productSuppliers')->searchable(false)->title(__('field.suppliers'))->addClass('none'),
            Column::make('barcode')->searchable(true)->title(__('field.barcode'))->addClass('none'),
            Column::make('supply_price')->searchable(true)->title(__('field.supply_price'))->addClass('none'),
            Column::make('retail_price')->searchable(true)->title(__('field.retail_price'))->addClass('none'),
            Column::computed('translation.text')->searchable(true)->title(__('field.description'))->addClass('none'),
            Column::computed('image')->searchable(false)->title(__('field.image'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
