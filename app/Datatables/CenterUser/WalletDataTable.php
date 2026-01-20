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
                    'show-user-to-wallet' => true,
                    'add-user-to-wallet' => true,
                    'edit' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                $html = view()->make('_partials.center_actions', compact('id', 'route', 'options', 'model'))->render();
                return $html;
            })
            ->editColumn('users.user.first_name', function ($row) {
                $users = '';
                foreach ($row->users as $user) {
                    $users .= '<span class="badge"
                        style="border-radius:10px;background-color:blue;margin:5px;padding:7px;">' . $user?->user?->name . '</span>';
                }
                return $users;
            })
            ->editColumn('amount', function ($row) {
                return $row->amount . ' ' . trim(get_currency());
            })
            ->editColumn('invoiced_amount', function ($row) {
                return $row->invoiced_amount . ' ' . trim(get_currency());
            })
            ->editColumn('used', function ($row) {
                return $row->used ? __('general.yes') : __('general.no');
            })
            ->editColumn('status', function ($row) {
                $checked = $row->deleted_at ? '' : 'checked';
                $operation = $row->deleted_at ? DeleteActionEnum::RESTORE_DELETED->value : DeleteActionEnum::SOFT_DELETE->value;
                return '<label class="switch switch-square">
                            <input onChange="changeStatus(\'' . $this->model . '\',\'' . $row->id . '\',\'' . $operation . '\')"
                                type="checkbox" class="switch-input"' . $checked . '>
                            <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->editColumn('code', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->code ?? '');
            })
            ->rawColumns(['users.user.first_name', 'status', 'code'], true)
            ->setRowId('id');
    }

    public function query(Wallet $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with(['users' => function ($q) {
            return $q->with(['user']);
        }])->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.create');

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
                    'text' => __('general.export'),  'className' => $buttonClass,
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
            ->addTableClass('table table-bordered table-hover')
            ->initComplete('function () {
             $(".dt-action-buttons").append("<a href=' . $addRoute . ' class=\"btn btn-primary btn-sm mx-1 mx-md-2 px-2 px-md-3 py-1 py-md-2\">' . __('general.add_new') . '<i class=\"ti ti-plus\"></i></a>");
            }')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == "ar" ?
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/ar.json" :
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#'),
            Column::make('code')->searchable(true)->title(__('field.code')),
            Column::computed('users.user.first_name')->searchable(true)->title(__('field.users')),
            Column::computed('amount')->searchable(true)->title(__('field.amount')),
            Column::computed('invoiced_amount')->searchable(true)->title(__('field.invoiced_amount')),
            Column::computed('used')->searchable(true)->title(__('field.used')),
            Column::make('start_at')->searchable(true)->title(__('field.start_at')),
            Column::make('end_at')->searchable(true)->title(__('field.end_at')),
            Column::computed('status')->searchable(false)->title(__('field.status')),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
