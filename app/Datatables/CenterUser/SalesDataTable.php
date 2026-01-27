<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesDataTable extends DataTable
{
    private $model = 'Sale';
    private $plural = 'sales';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'show' => true,
                    'print' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                $html = view()->make('_partials.center_actions', compact('id', 'route', 'options', 'model'))->render();
                return $html;
            })
            ->editColumn('branch.translation.name', function ($row) {
                return $row->branch?->translation?->name ?? '-';
            })
            ->editColumn('services', function ($row) {
                $serviceNames = [];

                foreach ($row->bookings as $booking) {
                    foreach ($booking->details as $detail) {
                        $name = $detail->service?->translation?->name ?? $detail->service?->name ?? null;
                        if ($name && !in_array($name, $serviceNames, true)) {
                            $serviceNames[] = $name;
                        }
                    }
                }

                return empty($serviceNames) ? '-' : implode(', ', $serviceNames);
            })
            ->editColumn('client.name', function ($row) {
                return $row->client?->name ?? __('general.walk_in');
            })
            ->addColumn('client_mobile', function ($row) {
                if (!$row->client) {
                    return '-';
                }
                return $row->client->full_phone ?? $row->client->phone ?? '-';
            })
            ->addColumn('payment_type', function ($row) {
                $types = [];

                foreach ($row->bookings as $booking) {
                    if ($booking->payment_type && !in_array($booking->payment_type, $types, true)) {
                        $types[] = $booking->payment_type;
                    }
                }

                foreach ($row->buyProducts as $buyProduct) {
                    if ($buyProduct->payment_type && !in_array($buyProduct->payment_type, $types, true)) {
                        $types[] = $buyProduct->payment_type;
                    }
                }

                return empty($types) ? '-' : implode(', ', $types);
            })
            ->editColumn('worker.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->worker?->name ?? '-');
            })
            ->editColumn('client.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->client?->name ?? __('general.walk_in'));
            })
            ->editColumn('tip', function ($row) {
                return $row->tip > 0 ? number_format($row->tip, 2) . ' ' . trim(get_currency()) : '-';
            })
            ->editColumn('total', function ($row) {
                return number_format($row->total, 2) . ' ' . trim(get_currency());
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
            ->rawColumns(['status', 'worker.name', 'client.name'], true)
            ->setRowId('id');
    }

    public function query(Sale $model): QueryBuilder
    {
        $user = auth('center_user')->user();
        $branchId = $user->branch_id ?? null;

        $query = $model->query()->with([
            'worker',
            'client',
            'branch.translation',
            'saleItems',
            'bookings.details.service.translation',
            'buyProducts',
        ])->withTrashed();

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $query->when(request()->has('branch_id'), function ($query) {
            $query->where('branch_id', request()->input('branch_id'));
        });

        return $query->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.cart');

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
            Column::computed('branch.translation.name')->searchable(true)->title(__('field.branch')),
            Column::computed('services')->searchable(true)->title(__('field.services')),
            Column::computed('client.name')->searchable(true)->title(__('field.client')),
            Column::computed('client_mobile')->searchable(true)->title(__('field.phone')),
            Column::computed('payment_type')->searchable(true)->title(__('field.payment_method')),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
            Column::computed('worker.name')->searchable(true)->title(__('field.worker')),
            Column::computed('tip')->searchable(false)->title(__('field.tip')),
            Column::computed('total')->searchable(false)->title(__('field.total')),
            Column::computed('status')->searchable(false)->title(__('field.status')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}

