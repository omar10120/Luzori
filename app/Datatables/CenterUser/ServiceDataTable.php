<?php
namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ServiceDataTable extends DataTable
{
    private $model = 'Service';
    private $plural = 'services';

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
            ->editColumn('price', function ($row) {
                return $row->price . ' ' . trim(get_currency());
            })
            ->editColumn('is_top', function ($row) {
                return $row->is_top ? __('general.yes') : __('general.no');
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
                return '<img src="' . $row->image . '" alt="">';
            })
            ->editColumn('translation.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->translation->name ?? '');
            })
            ->editColumn('translation.description', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->translation->description ?? '');
            })
            ->editColumn('category', function ($row) {
                return $row->category->translation->name ?? $row->category->name ?? __('general.none');
            })
            ->rawColumns(['action', 'image', 'status', 'translation.name', 'translation.description', 'category'], true)
            ->setRowId('id');
    }

    public function query(Service $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with(['translation', 'category.translation'])->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        // Check if user has permission to create services
        
        // Build initComplete JavaScript - conditionally include add button
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
        return app()->getLocale() == "ar" ?
        asset('js/lang/ar_custom.json') :
        "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    public function getColumns(): array
    {
        return [
            // Main visible columns
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('dtr-control all'),
            Column::computed('image')->searchable(false)->title(__('field.image'))->responsivePriority(2)->addClass('all'),
            Column::computed('translation.name')->searchable(true)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::computed('category')->searchable(false)->title(__('field.category'))->responsivePriority(3)->addClass('all'),
            Column::computed('price')->searchable(true)->title(__('field.price'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->responsivePriority(3)->addClass('all'),
            // Always expand into child row (so whole-row click always reveals details)
            Column::computed('translation.description')->searchable(true)->title(__('field.description'))->visible(false),
            Column::make('rooms_no')->searchable(true)->title(__('field.rooms_no'))->visible(false),
            Column::make('free_book')->searchable(true)->title(__('field.free_book'))->visible(false),
            Column::make('sort_order')->searchable(true)->title(__('field.sort_order'))->visible(false),
            Column::computed('is_top')->searchable(true)->title(__('field.is_top'))->visible(false),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->visible(false),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
