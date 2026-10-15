<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Package;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PackageDataTable extends DataTable
{
    private $model = 'Package';
    private $plural = 'packages';

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
                    'add-user-to-package' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->addColumn('name', function ($row) {
                return '<span class="lz-package-name">' . e($row->name ?? '—') . '</span>';
            })
            ->addColumn('paid_services', function ($row) {
                $services = '';
                foreach ($row->packageServicePaid as $service) {
                    $name = $service->service?->name ?? null;
                    if (!$name) {
                        continue;
                    }
                    $services .= '<span class="lz-service-badge lz-service-paid">' . e($name) . '</span>';
                }
                return $services !== '' ? $services : '—';
            })
            ->addColumn('free_services', function ($row) {
                $services = '';
                foreach ($row->packageServiceFree as $service) {
                    $name = $service->service?->name ?? null;
                    if (!$name) {
                        continue;
                    }
                    $services .= '<span class="lz-service-badge lz-service-free">' . e($name) . '</span>';
                }
                return $services !== '' ? $services : '—';
            })
            ->addColumn('status', function ($row) {
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
            ->editColumn('price', function ($row) {
                return '<span class="lz-amount">' . e(number_format((float) $row->price, 2)) . ' ' . e(trim(get_currency())) . '</span>';
            })
            ->addColumn('total_value', function ($row) {
                return '<span class="lz-total-value">' . e(number_format((float) ($row->total_value ?? 0), 2)) . ' ' . e(trim(get_currency())) . '</span>';
            })
            ->addColumn('users', function ($row) {
                $users = '';
                foreach ($row->usersPackages as $userPackage) {
                    if ($userPackage->user) {
                        $users .= '<span class="lz-user-badge">' . e($userPackage->user->name) . '</span>';
                    }
                }
                return $users !== '' ? $users : '<span class="text-muted">' . e(__('general.no_users')) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                // CreatedAtTrait already returns a formatted string
                return e((string) ($row->created_at ?: '—'));
            })
            ->rawColumns([
                'action',
                'paid_services',
                'free_services',
                'status',
                'name',
                'total_value',
                'users',
                'price',
            ])
            ->setRowId('id');
    }

    public function query(Package $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with([
            'translation',
            'usersPackages.user',
            'packageServicePaid.service.translation',
            'packageServiceFree.service.translation',
        ])->orderBy($this->plural . '.id', 'DESC');
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
            Column::computed('name')->searchable(true)->orderable(false)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::make('price')->searchable(true)->title(__('field.price'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->orderable(false)->title(__('field.status'))->responsivePriority(2)->addClass('all'),
            Column::computed('total_value')->searchable(false)->orderable(false)->title(__('field.total_services_price'))->addClass('none'),
            Column::computed('paid_services')->searchable(false)->orderable(false)->title(__('field.paid_services'))->addClass('none'),
            Column::computed('free_services')->searchable(false)->orderable(false)->title(__('field.free_services'))->addClass('none'),
            Column::computed('users')->searchable(false)->orderable(false)->title(__('field.users'))->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
