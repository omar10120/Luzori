<?php

namespace App\Datatables\Admin;

use App\Enums\DeleteActionEnum;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BannerDataTable extends DataTable
{
    private string $model = 'Banner';
    private string $plural = 'banners';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'admin.' . $this->plural;
                $options = [
                    'edit' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                return view()->make('_partials.actions', [
                    'id' => $item->id,
                    'route' => $route,
                    'options' => $options,
                    'model' => $this->model,
                ])->render();
            })
            ->editColumn('image', fn ($row) => '<img src="' . e($row->image) . '" alt="Banner" style="width:120px;height:55px;object-fit:cover;">')
            ->editColumn('placement_type', fn ($row) => '<span class="badge bg-label-info">' . e(str_replace('_', ' ', ucfirst($row->placement_type))) . '</span>')
            ->editColumn('link_type', fn ($row) => '<span class="badge bg-label-primary">' . e(ucfirst($row->link_type)) . '</span>')
            ->addColumn('target', function ($row) {
                return match ($row->link_type) {
                    'external' => e($row->external_url ?: '-'),
                    'category' => e($row->globalCategory?->name ?? 'Category #' . $row->global_category_id),
                    'center' => e($row->center?->name ?? 'Center #' . $row->center_id),
                    'service' => e(($row->center?->name ?? 'Center #' . $row->center_id) . ' / Service #' . $row->service_id),
                    default => '-',
                };
            })
            ->editColumn('is_active', fn ($row) => $row->is_active
                ? '<span class="badge bg-label-success">Active</span>'
                : '<span class="badge bg-label-secondary">Inactive</span>')
            ->rawColumns(['action', 'image', 'placement_type', 'link_type', 'is_active'])
            ->setRowId('id');
    }

    public function query(Banner $model): QueryBuilder
    {
        return $model->newQuery()
            ->withTrashed()
            ->with(['center', 'globalCategory'])
            ->orderByDesc('sort_order')
            ->orderByDesc('id');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-2 px-4';
        $addRoute = route('admin.' . $this->plural . '.create');

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
            ->addAction(['printable' => false, 'exportable' => false, 'className' => 'dt-center', 'title' => __('general.actions')])
            ->buttons([
                Button::make('colvis')->addClass($buttonClass . ' btn-warning')->text(__('general.column_visibility')),
                [
                    'extend' => 'collection',
                    'text' => __('general.export'),
                    'className' => $buttonClass,
                    'buttons' => ['excel', 'csv', 'pdf', 'print', 'copy'],
                ],
            ])
            ->language($this->getDataTableLanguageUrl())
            ->addTableClass('table table-bordered table-hover')
            ->initComplete('function () {
                $(".dt-action-buttons").append("<a href=\'' . $addRoute . '\' class=\"btn btn-primary mx-2\">' . __('general.add_new') . '<i class=\"ti ti-plus\"></i></a>");
            }')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() === 'ar'
            ? asset('js/lang/ar_custom.json')
            : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json';
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::computed('image')->searchable(false)->title(__('field.image')),
            Column::make('title')->title(__('field.title')),
            Column::computed('placement_type')->searchable(false)->title(__('field.placement')),
            Column::computed('link_type')->searchable(false)->title(__('field.link_type')),
            Column::computed('target')->searchable(false)->title(__('field.target')),
            Column::make('sort_order')->title(__('field.sort_order')),
            Column::computed('is_active')->searchable(false)->title(__('field.status')),
            Column::make('created_at')->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
