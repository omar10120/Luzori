<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SuppliersDataTable extends DataTable
{
    private $model = 'Supplier';
    private $plural = 'suppliers';

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
            ->editColumn('logo', function ($row) {
                return '<img src="' . e($row->logo_url) . '" alt="Logo" class="lz-supplier-logo">';
            })
            ->editColumn('name', function ($row) {
                return '<span class="lz-supplier-name">' . e($row->name ?? '—') . '</span>';
            })
            ->editColumn('email', function ($row) {
                return e($row->email ?: '—');
            })
            ->editColumn('phone', function ($row) {
                return e($row->phone ?: '—');
            })
            ->editColumn('description', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->description ?? '');
            })
            ->rawColumns(['logo', 'name', 'description', 'action'], true)
            ->setRowId('id');
    }

    public function query(Supplier $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';
        $addRoute = route('center_user.suppliers.create');

        $initCompleteJs = 'function () {';
        $initCompleteJs .= 'var $lzAct=$(".dt-action-buttons").first();';
        $initCompleteJs .= '$lzAct.append("<a href=\'' . $addRoute . '\' class=\"btn btn-luzori-primary btn-sm mx-1 px-3 py-2\">' . __('general.add_new') . ' <i class=\"ti ti-plus\"></i></a>");';
        $initCompleteJs .= '}';

        return $this->builder()
            ->setTableId('suppliers-table')
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
        return app()->getLocale() == 'ar'
            ? asset('js/lang/ar_custom.json')
            : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json';
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('all'),
            Column::computed('logo')->searchable(false)->title(__('field.logo'))->responsivePriority(2)->addClass('all'),
            Column::make('name')->searchable(true)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::make('email')->searchable(true)->title(__('field.email'))->responsivePriority(2)->addClass('all'),
            Column::make('phone')->searchable(true)->title(__('field.phone_number'))->responsivePriority(2)->addClass('all'),
            Column::computed('description')->searchable(false)->title(__('field.description'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
