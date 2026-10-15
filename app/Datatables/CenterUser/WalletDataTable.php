<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WalletDataTable extends DataTable
{
    private $model = 'Wallet';
    private $plural = 'wallets';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'wallet-sales-history' => true,
                    'add-user-to-wallet' => true,
                    'edit' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->addColumn('users_list', function ($row) {
                $users = '';
                foreach ($row->users as $user) {
                    $name = $user?->user?->name;
                    if (!$name) {
                        continue;
                    }
                    $users .= '<span class="lz-user-badge">' . e($name) . '</span>';
                }
                return $users !== '' ? $users : '—';
            })
            ->editColumn('amount', function ($row) {
                return '<span class="lz-amount">' . e(number_format((float) $row->amount, 2)) . ' ' . e(trim(get_currency())) . '</span>';
            })
            ->editColumn('invoiced_amount', function ($row) {
                return e(number_format((float) ($row->invoiced_amount ?? 0), 2)) . ' ' . e(trim(get_currency()));
            })
            ->addColumn('used_label', function ($row) {
                $class = $row->used ? 'lz-used-yes' : 'lz-used-no';
                $label = $row->used ? __('general.yes') : __('general.no');
                return '<span class="lz-used-badge ' . $class . '">' . e($label) . '</span>';
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
            ->editColumn('code', function ($row) {
                return '<span class="lz-wallet-code">' . e($row->code ?? '—') . '</span>';
            })
            ->editColumn('serial_number', function ($row) {
                return e($row->serial_number ?: '—');
            })
            ->editColumn('start_at', function ($row) {
                return e($row->start_at ?: '—');
            })
            ->editColumn('end_at', function ($row) {
                return e($row->end_at ?: '—');
            })
            ->editColumn('created_at', function ($row) {
                // CreatedAtTrait already returns a formatted string
                return e((string) ($row->created_at ?: '—'));
            })
            ->rawColumns(['action', 'users_list', 'status', 'code', 'amount', 'used_label'])
            ->setRowId('id');
    }

    public function query(Wallet $model): QueryBuilder
    {
        $isAdmin = ((int) get_user_role() === 1);

        $query = $model->query()
            ->withTrashed()
            ->with([
                'created_by_user',
                'users' => function ($q) {
                    return $q->with(['user']);
                },
            ]);

        if (!$isAdmin) {
            $query->forCenterUserBranch();
        }

        return $query->orderBy($this->plural . '.id', 'DESC');
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
            Column::make('code')->searchable(true)->title(__('field.code'))->responsivePriority(1)->addClass('all'),
            Column::make('amount')->searchable(true)->title(__('field.amount'))->responsivePriority(2)->addClass('all'),
            Column::computed('used_label')->searchable(false)->orderable(false)->title(__('field.used'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->orderable(false)->title(__('field.status'))->responsivePriority(2)->addClass('all'),
            Column::make('serial_number')->searchable(true)->title(__('field.serial_number'))->addClass('none'),
            Column::computed('users_list')->searchable(false)->orderable(false)->title(__('field.users'))->addClass('none'),
            Column::make('invoiced_amount')->searchable(true)->title(__('field.invoiced_amount'))->addClass('none'),
            Column::make('start_at')->searchable(true)->title(__('field.start_at'))->addClass('none'),
            Column::make('end_at')->searchable(true)->title(__('field.end_at'))->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
