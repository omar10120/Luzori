<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MembershipDataTable extends DataTable
{
    private $model = 'Membership';
    private $plural = 'memberships';

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
            ->editColumn('image', function ($row) {
                $src = e($row->image ?: asset('assets/img/avatars/1.png'));
                return '<img src="' . $src . '" alt="" class="lz-membership-thumb">';
            })
            ->editColumn('user.first_name', function ($row) {
                $name = $row->user?->name ?? '—';
                return '<span class="lz-customer-name">' . e($name) . '</span>';
            })
            ->editColumn('user.phone', function ($row) {
                return e($row->user?->full_phone ?? '—');
            })
            ->editColumn('membership_no', function ($row) {
                return '<span class="lz-membership-no">' . e($row->membership_no ?? '—') . '</span>';
            })
            ->editColumn('percent', function ($row) {
                return '<span class="lz-percent-badge">' . e((string) ($row->percent ?? '0')) . '%</span>';
            })
            ->editColumn('created_user.name', function ($row) {
                return e($row->created_user?->name ?? '—');
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
            ->editColumn('start_at', function ($row) {
                return e($row->start_at ?: '—');
            })
            ->editColumn('end_at', function ($row) {
                return e($row->end_at ?: '—');
            })
            ->editColumn('created_at', function ($row) {
                try {
                    return $row->created_at ? $row->created_at->diffForHumans() : '—';
                } catch (\Throwable $e) {
                    return e((string) ($row->created_at ?? '—'));
                }
            })
            ->rawColumns(['action', 'status', 'user.first_name', 'image', 'membership_no', 'percent'])
            ->setRowId('id');
    }

    public function query(Membership $model): QueryBuilder
    {
        return $model->query()
            ->with(['user', 'created_user', 'media'])
            ->withTrashed()
            ->orderBy('memberships_cards.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
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
                'className' => 'dt-center lz-actions-col all',
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
            Column::computed('image')->searchable(false)->orderable(false)->title(__('field.image'))->responsivePriority(2)->addClass('all'),
            Column::make('user.first_name')->searchable(true)->title(__('field.customer_name'))->responsivePriority(1)->addClass('all'),
            Column::make('membership_no')->searchable(true)->title(__('field.membership_no'))->responsivePriority(2)->addClass('all'),
            Column::computed('percent')->searchable(false)->orderable(false)->title(__('field.percent'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->orderable(false)->title(__('field.status'))->responsivePriority(2)->addClass('all'),
            Column::computed('user.phone')->searchable(false)->orderable(false)->title(__('field.customer_phone'))->addClass('none'),
            Column::make('start_at')->searchable(true)->title(__('field.start_at'))->addClass('none'),
            Column::make('end_at')->searchable(true)->title(__('field.end_at'))->addClass('none'),
            Column::computed('created_user.name')->searchable(false)->orderable(false)->title(__('field.created_by'))->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
