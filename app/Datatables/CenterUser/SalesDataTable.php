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
                $serviceDetails = [];
                $count = 0;

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'booking' && $saleItem->itemable) {
                        $booking = $saleItem->itemable;
                        foreach ($booking->details as $detail) {
                            $count++;
                            $serviceName = $detail->service?->translation?->name ?? $detail->service?->name ?? 'N/A';
                            $workerName = $detail->worker?->name ?? '-';
                            $date = $detail->_date ?? $booking->booking_date ?? '-';
                            $time = ($detail->from_time ?? '') . '-' . ($detail->to_time ?? '');
                            $price = number_format($detail->price ?? 0, 2) . ' ' . trim(get_currency());
                            
                            $serviceDetails[] = [
                                'service' => $serviceName,
                                'worker' => $workerName,
                                'date' => $date,
                                'time' => $time,
                                'price' => $price
                            ];
                        }
                    }
                }

                if (empty($serviceDetails)) {
                    return '-';
                }

                $html = '<span class="badge bg-label-primary">' . $count . ' ' . __('locale.bookings') . '</span>';
                $html .= ' <button type="button" class="btn btn-sm btn-outline-primary ms-2 view-booking-details" data-sale-id="' . $row->id . '" data-modal-title="' . e(__('field.booking_details')) . '" data-details="' . e(json_encode($serviceDetails)) . '">';
                $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                $html .= '</button>';

                return $html;
            })
            ->addColumn('products', function ($row) {
                $productDetails = [];
                $count = 0;

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'buy_product' && $saleItem->itemable) {
                        $buyProduct = $saleItem->itemable;
                        foreach ($buyProduct->details as $detail) {
                            $count++;
                            $productName = $detail->product?->translation?->name ?? $detail->product?->name ?? 'N/A';
                            $price = number_format($detail->price ?? 0, 2) . ' ' . trim(get_currency());
                            
                            $productDetails[] = [
                                'product' => $productName,
                                'price' => $price
                            ];
                        }
                    }
                }

                if (empty($productDetails)) {
                    return '-';
                }

                $html = '<span class="badge bg-label-success">' . $count . ' ' . __('locale.products') . '</span>';
                $html .= ' <button type="button" class="btn btn-sm btn-outline-success ms-2 view-product-details" data-sale-id="' . $row->id . '" data-modal-title="' . e(__('locale.products')) . ' ' . e(__('general.show')) . '" data-details="' . e(json_encode($productDetails)) . '">';
                $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                $html .= '</button>';

                return $html;
            })
            ->addColumn('coupons', function ($row) {
                $couponDetails = [];
                $count = 0;

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'user_wallet' && $saleItem->itemable) {
                        $count++;
                        $userWallet = $saleItem->itemable;
                        $walletCode = $userWallet->wallet?->code ?? '-';
                        $amount = number_format($userWallet->amount ?? 0, 2) . ' ' . trim(get_currency());
                        $walletType = $userWallet->wallet_type ?? '-';
                        $userName = $userWallet->user?->name ?? '-';
                        
                        $couponDetails[] = [
                            'code' => $walletCode,
                            'amount' => $amount,
                            'type' => $walletType,
                            'user' => $userName
                        ];
                    }
                }

                if (empty($couponDetails)) {
                    return '-';
                }

                $html = '<span class="badge bg-label-warning">' . $count . ' ' . __('field.coupons') . '</span>';
                $html .= ' <button type="button" class="btn btn-sm btn-outline-warning ms-2 view-coupon-details" data-sale-id="' . $row->id . '" data-modal-title="' . e(__('field.coupons')) . ' ' . e(__('general.show')) . '" data-details="' . e(json_encode($couponDetails)) . '">';
                $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                $html .= '</button>';

                return $html;
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
            ->addColumn('booking_payment', function ($row) {
                $types = [];

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'booking' && $saleItem->itemable) {
                        $booking = $saleItem->itemable;
                        if ($booking->payment_type && !in_array($booking->payment_type, $types, true)) {
                            $types[] = $booking->payment_type;
                        }
                    }
                }

                return empty($types) ? '-' : implode(', ', $types);
            })
            ->addColumn('product_payment', function ($row) {
                $types = [];

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'buy_product' && $saleItem->itemable) {
                        $buyProduct = $saleItem->itemable;
                        if ($buyProduct->payment_type && !in_array($buyProduct->payment_type, $types, true)) {
                            $types[] = $buyProduct->payment_type;
                        }
                    }
                }

                return empty($types) ? '-' : implode(', ', $types);
            })
            ->addColumn('coupon_payment', function ($row) {
                $types = [];

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'user_wallet' && $saleItem->itemable) {
                        $userWallet = $saleItem->itemable;
                        if ($userWallet->wallet_type && !in_array($userWallet->wallet_type, $types, true)) {
                            $types[] = $userWallet->wallet_type;
                        }
                    }
                }

                return empty($types) ? '-' : implode(', ', $types);
            })
            ->editColumn('worker.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->worker?->name ?? '-');
            })
            ->addColumn('booking_employees', function ($row) {
                $employees = [];

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'booking' && $saleItem->itemable) {
                        $booking = $saleItem->itemable;
                        foreach ($booking->details as $detail) {
                            if ($detail->worker && $detail->worker->name) {
                                $workerName = $detail->worker->name;
                                if (!in_array($workerName, $employees, true)) {
                                    $employees[] = $workerName;
                                }
                            }
                        }
                    }
                }

                return empty($employees) ? '-' : implode('<br>', $employees);
            })
            ->addColumn('product_employees', function ($row) {
                $employees = [];

                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'buy_product' && $saleItem->itemable) {
                        $buyProduct = $saleItem->itemable;
                        
                        // Add sales worker
                        if ($buyProduct->sales_worker && $buyProduct->sales_worker->name) {
                            $salesWorkerName = $buyProduct->sales_worker->name . ' (' . __('field.sales_worker') . ')';
                            if (!in_array($salesWorkerName, $employees, true)) {
                                $employees[] = $salesWorkerName;
                            }
                        }
                        
                        // Add worker
                        if ($buyProduct->worker && $buyProduct->worker->name) {
                            $workerName = $buyProduct->worker->name . ' (' . __('field.worker') . ')';
                            if (!in_array($workerName, $employees, true)) {
                                $employees[] = $workerName;
                            }
                        }
                    }
                }

                return empty($employees) ? '-' : implode('<br>', $employees);
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
            ->rawColumns(['status', 'worker.name', 'client.name', 'services', 'products', 'coupons', 'booking_employees', 'product_employees'], true)
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
            'saleItems' => function($q) {
                $q->with([
                    'itemable' => function($q) {
                        $model = $q->getModel();
                        if ($model instanceof \App\Models\Booking) {
                            $q->with(['details.service.translation', 'details.worker']);
                        } elseif ($model instanceof \App\Models\BuyProduct) {
                            $q->with(['details.product.translation', 'sales_worker', 'worker']);
                        } elseif ($model instanceof \App\Models\UserWallet) {
                            $q->with(['wallet', 'user', 'worker']);
                        }
                    }
                ]);
            },
            'bookings.details.service.translation',
            'bookings.details.worker',
            'buyProducts.details.product.translation',
            'buyProducts.sales_worker',
            'buyProducts.worker',
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
            Column::computed('services')->searchable(false)->title(__('field.services') . ' (' . __('locale.bookings') . ')'),
            Column::computed('products')->searchable(false)->title(__('locale.products')),
            Column::computed('coupons')->searchable(false)->title(__('field.coupons')),
            Column::computed('client.name')->searchable(true)->title(__('field.client')),
            Column::computed('client_mobile')->searchable(true)->title(__('field.phone')),
            Column::computed('booking_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('locale.bookings') . ')'),
            Column::computed('product_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('locale.products') . ')'),
            Column::computed('coupon_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('field.coupons') . ')'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
            Column::computed('booking_employees')->searchable(false)->title(__('field.employee') . ' (' . __('locale.bookings') . ')'),
            Column::computed('product_employees')->searchable(false)->title(__('field.employee') . ' (' . __('locale.products') . ')'),
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



