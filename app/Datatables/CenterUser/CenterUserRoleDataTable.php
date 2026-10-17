<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CenterUserRoleDataTable extends DataTable
{
    private $model = 'Role';
    private $plural = 'centeruserroles';

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
                    'with_trashed' => 0,
                ];
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->addColumn('permission_count', function ($row) {
                $count = $row->permissions->count();
                return '<span class="lz-perm-count">' . (int) $count . ' ' . e(__('field.permission')) . '</span>';
            })
            ->addColumn('permission', function ($row) {
                $locale = app()->getLocale();
                $permissions = '';
                foreach ($row->permissions as $permission) {
                    $name = ($locale == 'ar') ? ($permission->name_ar ?? $permission->name) : $permission->name;
                    $permissions .= '<span class="lz-perm-badge">' . e($name) . '</span>';
                }
                return $permissions !== '' ? $permissions : '—';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->diffForHumans() : '—';
            })
            ->editColumn('name', function ($row) {
                return '<span class="lz-role-name">' . e($row->name ?? '—') . '</span>';
            })
            ->rawColumns(['action', 'permission', 'permission_count', 'name'], true)
            ->setRowId('id');
    }

    public function query(Role $model): QueryBuilder
    {
        return $model->query()
            ->with('permissions')
            ->where('guard_name', 'center_api')
            ->orderBy('roles.id', 'DESC');
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
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('all'),
            Column::make('name')->searchable(true)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::computed('permission_count')->searchable(false)->title(__('field.permission'))->responsivePriority(2)->addClass('all lz-details-skip'),
            Column::computed('permission')->searchable(false)->title(__('field.permission'))->visible(false),
            Column::computed('created_at')->searchable(false)->title(__('field.created_at'))->visible(false),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
