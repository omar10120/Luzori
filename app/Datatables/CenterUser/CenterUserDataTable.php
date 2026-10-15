<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\CenterUser;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CenterUserDataTable extends DataTable
{
    private $model = 'CenterUser';
    private $plural = 'centerusers';

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
            ->editColumn('statusWeb', function ($row) {
                $checked = $row->statusWeb ? 'checked' : '';
                return '<label class="switch switch-square" onclick="event.stopPropagation()">
                            <input onChange="changeStatusWeb(\'' . $row->id . '\')"
                                type="checkbox" class="switch-input"' . $checked . '>
                            <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->editColumn('statusMobile', function ($row) {
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
            ->editColumn('role', function ($row) {
                $role = $row->roles()?->first()?->name;
                if (!$role) {
                    return '—';
                }
                return '<span class="lz-role-badge">' . e($role) . '</span>';
            })
            ->editColumn('name', function ($row) {
                return '<span class="lz-staff-name">' . e($row->name ?? '—') . '</span>';
            })
            ->editColumn('email', function ($row) {
                return e($row->email ?: '—');
            })
            ->editColumn('phone', function ($row) {
                return e($row->phone ?: '—');
            })
            ->rawColumns(['action', 'statusWeb', 'statusMobile', 'name', 'role'], true)
            ->setRowId('id');
    }

    public function query(CenterUser $model): QueryBuilder
    {
        $user = auth('center_user')->user();
        if (get_user_role() != 1) {
            $branchId = $user->branch_id ?? null;
        } else {
            $branchId = null;
        }
        $query = $model->query()->withTrashed()->with(['media', 'roles']);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        return $query->orderBy('center_users.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

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
            Column::make('email')->searchable(true)->title(__('field.email'))->responsivePriority(2)->addClass('all'),
            Column::make('phone')->searchable(true)->title(__('field.phone'))->responsivePriority(2)->addClass('all'),
            Column::make('currency')->searchable(true)->title(__('field.currency'))->responsivePriority(3)->addClass('all'),
            Column::computed('role')->searchable(false)->title(__('field.role'))->responsivePriority(2)->addClass('all'),
            Column::computed('statusWeb')->searchable(false)->title(__('field.statusWeb'))->responsivePriority(2)->addClass('all'),
            Column::computed('statusMobile')->searchable(false)->title(__('field.statusMobile'))->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
