<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    private $model = 'User';
    private $plural = 'users';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'edit' => true,
                    'show' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->editColumn('first_name', function ($row) {
                return '<span class="lz-user-name">' . e($row->name ?? '—') . '</span>';
            })
            ->editColumn('phone', function ($row) {
                $phone = trim(($row->country_code ?? '') . ($row->phone ?? ''));
                return e($phone !== '' ? $phone : '—');
            })
            ->editColumn('email', function ($row) {
                return e($row->email ?: '—');
            })
            ->editColumn('wallet', function ($row) {
                return '<span class="lz-wallet-num">' . e($row->wallet ?? 0) . '</span>';
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
            ->editColumn('packages', function ($row) {
                $count = $row->packages->where('status', 'active')->count();
                return '<span class="lz-pkg-badge">' . (int) $count . ' ' . e(__('locale.packages')) . '</span>';
            })
            ->editColumn('is_default', function ($row) {
                return $row->is_default
                    ? '<span class="lz-yes-badge">' . e(__('field.yes')) . '</span>'
                    : '<span class="lz-no-badge">' . e(__('field.no')) . '</span>';
            })
            ->rawColumns(['action', 'status', 'first_name', 'packages', 'is_default', 'wallet'], true)
            ->setRowId('id');
    }

    public function query(User $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with(['media', 'packages' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy($this->plural . '.id', 'DESC');
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
            Column::computed('first_name')->searchable(true)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::make('email')->searchable(true)->title(__('field.email'))->responsivePriority(2)->addClass('all'),
            Column::computed('phone')->searchable(true)->title(__('field.phone'))->responsivePriority(2)->addClass('all'),
            Column::make('wallet')->searchable(true)->title(__('field.wallet'))->responsivePriority(2)->addClass('all'),
            Column::computed('packages')->searchable(false)->title(__('locale.packages'))->responsivePriority(2)->addClass('all'),
            Column::computed('is_default')->searchable(false)->title(__('field.is_default'))->responsivePriority(3)->addClass('all'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->visible(false),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->visible(false),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
