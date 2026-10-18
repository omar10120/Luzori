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

    /** @var array<int, array> */
    private array $salePanelCache = [];

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $dataTable = (new EloquentDataTable($query));
        
        $dataTable->filter(function ($query) {
            $keyword = request()->input('search.value');
            if (empty($keyword)) {
                return;
            }
            
            $keyword = trim($keyword);
            $isNumeric = is_numeric($keyword);
            $isShortNumeric = $isNumeric && strlen($keyword) <= 5;
            
            $like = '%' . strtolower($keyword) . '%';
            $phoneLike = '%' . $keyword . '%';
            $keywordWithoutPlus = ltrim($keyword, '+');
            $phoneLikeWithoutPlus = '%' . $keywordWithoutPlus . '%';
            
            if ($isShortNumeric) {
                $query->where('sales.id', '=', (int)$keyword);
            } else {
                $query->where(function ($q) use ($like, $phoneLike, $phoneLikeWithoutPlus, $isNumeric, $keyword, $keywordWithoutPlus) {
                    if ($isNumeric) {
                        $q->where('sales.id', '=', (int)$keyword)
                          ->orWhereRaw('CAST(sales.id AS CHAR) LIKE ?', [$phoneLike]);
                    } else {
                        $q->whereRaw('LOWER(CAST(sales.id AS CHAR)) LIKE ?', [$like]);
                    }
                    
                    $q->orWhereHas('client', function ($subQ) use ($like) {
                        $subQ->whereRaw('LOWER(first_name) LIKE ?', [$like])
                             ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                             ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', [$like]);
                    });
                    
                    $q->orWhereHas('client', function ($subQ) use ($phoneLike, $phoneLikeWithoutPlus, $keywordWithoutPlus) {
                        $subQ->where(function ($phoneQ) use ($phoneLike, $phoneLikeWithoutPlus, $keywordWithoutPlus) {
                            $phoneQ->whereRaw('CONCAT(country_code, phone) LIKE ?', [$phoneLike])
                                   ->orWhereRaw('CONCAT(country_code, phone) LIKE ?', [$phoneLikeWithoutPlus])
                                   ->orWhereRaw('phone LIKE ?', [$phoneLike])
                                   ->orWhereRaw('phone LIKE ?', [$phoneLikeWithoutPlus])
                                   ->orWhereRaw('CONCAT(REPLACE(country_code, "+", ""), phone) LIKE ?', ['%' . $keywordWithoutPlus . '%']);
                        });
                    });
                    
                    if (!$isNumeric) {
                        $q->orWhereHas('branch.translation', function ($subQ) use ($like) {
                            $subQ->whereRaw('LOWER(name) LIKE ?', [$like]);
                        })
                        ->orWhereHas('worker', function ($subQ) use ($like) {
                            $subQ->whereRaw('LOWER(name) LIKE ?', [$like]);
                        })
                        ->orWhereRaw('LOWER(CAST(sales.created_at AS CHAR)) LIKE ?', [$like]);
                    }
                });
            }
        });
        
        return $dataTable
            ->editColumn('action', function ($item) {
                try {
                    $route = 'center_user.' . $this->plural;
                    $id = $item->id;
                    $model = $this->model;
                    $canDelete = auth('center_user')->user()?->can('DELETE_SALES', 'center_api');
                    $options = [
                        'show_full_page' => true,
                        'print' => true,
                        'row_details' => false,
                        'sale_side_id' => (int) $item->id,
                        'operation' => DeleteActionEnum::FORCE_DELETE->value,
                        'with_trashed' => 1,
                    ];
                    try {
                        $options['sale_panel_b64'] = $this->salePanelB64($item);
                    } catch (\Throwable $e) {
                        // keep sale_side_id fallback for the drawer
                    }
                    if ($canDelete) {
                        $options['sale_otp_delete'] = true;
                    }

                    try {
                        $hasBooking = $item->bookings->isNotEmpty()
                            || $item->saleItems->contains(fn ($si) => $si->item_type === 'booking');
                        $hasTip = (float) ($item->tip ?? 0) > 0;
                        if (!$hasTip) {
                            foreach ($item->bookings as $booking) {
                                foreach ($booking->details ?? [] as $detail) {
                                    if ((float) ($detail->tip ?? 0) > 0) {
                                        $hasTip = true;
                                        break 2;
                                    }
                                }
                            }
                        }
                        if ($hasBooking && !$hasTip) {
                            $options['add_tip'] = true;
                        }
                    } catch (\Throwable $e) {
                        // ignore tip action errors
                    }

                    return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
                } catch (\Throwable $e) {
                    return '';
                }
            })
            ->editColumn('branch.translation.name', function ($row) {
                return $row->branch?->translation?->name ?? '-';
            })
            ->addColumn('services', function ($row) {
                try {
                    $details = $this->collectBookingDetails($row);
                    $count = count($details);
                    if ($count === 0) {
                        return '-';
                    }

                    $html = '<span class="lz-sale-badge is-booking">' . $count . ' ' . __('locale.bookings') . '</span>';
                    $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-section-dialog" data-section="bookings" data-modal-title="' . e(__('field.booking_details')) . '" data-details-b64="' . $this->jsonB64($details) . '">';
                    $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                    $html .= '</button>';

                    return $html;
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('products', function ($row) {
                try {
                    $details = $this->collectProductDetails($row);
                    $count = count($details);
                    if ($count === 0) {
                        return '-';
                    }

                    $html = '<span class="lz-sale-badge is-product">' . $count . ' ' . __('locale.products') . '</span>';
                    $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-section-dialog" data-section="products" data-modal-title="' . e(__('locale.products') . ' ' . __('general.show')) . '" data-details-b64="' . $this->jsonB64($details) . '">';
                    $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                    $html .= '</button>';

                    return $html;
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('packages', function ($row) {
                try {
                    $details = $this->collectPackageDetails($row);
                    $count = count($details);
                    if ($count === 0) {
                        return '-';
                    }

                    $packageLabel = $count === 1 ? __('locale.package') : __('locale.packages');
                    $html = '<span class="lz-sale-badge is-package">' . $count . ' ' . $packageLabel . '</span>';
                    $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-section-dialog" data-section="packages" data-modal-title="' . e(__('field.packages') . ' ' . __('general.show')) . '" data-details-b64="' . $this->jsonB64($details) . '">';
                    $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                    $html .= '</button>';

                    return $html;
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('coupons', function ($row) {
                try {
                    $details = $this->collectCouponDetails($row);
                    $count = count($details);
                    if ($count === 0) {
                        return '-';
                    }

                    $html = '<span class="lz-sale-badge is-coupon">' . $count . ' ' . __('field.coupons') . '</span>';
                    $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-section-dialog" data-section="coupons" data-modal-title="' . e(__('field.coupons') . ' ' . __('general.show')) . '" data-details-b64="' . $this->jsonB64($details) . '">';
                    $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                    $html .= '</button>';

                    return $html;
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('client_mobile', function ($row) {
                if (!$row->client) {
                    return '-';
                }
                return $row->client->full_phone ?? $row->client->phone ?? '-';
            })
            // Custom search for client name (users table has first_name/last_name, not name column)
            ->filterColumn('client.name', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $like = '%' . $keyword . '%';

                $query->whereHas('client', function ($q) use ($like) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(last_name) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', [$like]);
                });
            })
            // Custom search for client mobile (search by full_phone and phone)
            ->filterColumn('client_mobile', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $like = '%' . $keyword . '%';

                $query->whereHas('client', function ($q) use ($like) {
                    // country_code + phone
                    $q->whereRaw('LOWER(CONCAT(country_code, phone)) LIKE ?', [$like])
                      ->orWhereRaw('LOWER(phone) LIKE ?', [$like]);
                });
            })
            // Custom search for ID (ensure numeric search works correctly)
            ->filterColumn('id', function ($query, $keyword) {
                if (is_numeric($keyword)) {
                    $query->where('sales.id', '=', (int)$keyword);
                } else {
                    $query->whereRaw('CAST(sales.id AS CHAR) LIKE ?', ['%' . $keyword . '%']);
                }
            })
            ->addColumn('booking_payment', function ($row) {
                try {
                    $methods = [];
                    foreach ($row->bookings ?? [] as $booking) {
                        if (($booking->payment_type ?? null) === 'multiple' && !empty($booking->payment_methods)) {
                            foreach ($booking->payment_methods as $method) {
                                $methodName = $method['method'] ?? 'N/A';
                                $amount = number_format($method['amount'] ?? 0, 2);
                                $methods[] = $methodName . ' (' . $amount . ')';
                            }
                        } elseif (!empty($booking->payment_type) && !in_array($booking->payment_type, $methods, true)) {
                            $methods[] = $booking->payment_type;
                        }
                    }
                    return empty($methods) ? '-' : implode(', ', $methods);
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('booking_source', function ($row) {
                try {
                    $sources = [];
                    foreach ($row->bookings ?? [] as $booking) {
                        foreach ($booking->details ?? [] as $detail) {
                            $source = $detail->booking_source ?? 'inside_booking';
                            if (!in_array($source, $sources, true)) {
                                $sources[] = $source;
                            }
                        }
                    }

                    if (empty($sources)) {
                        return '-';
                    }

                    $html = '';
                    foreach ($sources as $source) {
                        if ($source === 'outside_booking') {
                            $html .= '<span class="badge bg-label-info mb-1">' . __('api.outside_booking') . '</span><br>';
                        } else {
                            $html .= '<span class="badge bg-label-secondary mb-1">' . __('api.inside_booking') . '</span><br>';
                        }
                    }
                    return $html;
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('status', function ($row) {
                try {
                    $booking = optional($row->bookings)->first();
                    if (!$booking) {
                        return '-';
                    }

                    $firstDetail = optional($booking->details)->first();
                    $status = $firstDetail->status ?? 'confirmed';
                    $source = $firstDetail->booking_source ?? 'inside_booking';

                    if ($source !== 'outside_booking') {
                        $deletedStatus = $booking->deleted_at ? 'cancelled' : 'confirmed';
                        $class = $booking->deleted_at ? 'bg-label-danger' : 'bg-label-success';
                        return '<span class="badge ' . $class . '">' . __('field.booking_status_' . $deletedStatus) . '</span>';
                    }

                    $map = [
                        'pending'   => 'bg-label-warning',
                        'confirmed' => 'bg-label-success',
                        'rejected'  => 'bg-label-danger',
                    ];
                    $class = $map[$status] ?? 'bg-label-secondary';
                    $html = '<span class="badge ' . $class . '">' . __('field.booking_status_' . $status) . '</span>';

                    $user = auth('center_user')->user();
                    if ($status === 'pending' && $user && $user->can('UPDATE_BOOKINGS', 'center_api')) {
                        $id = $booking->id;
                        $confirmUrl = route('center_user.bookings.confirm');
                        $rejectUrl = route('center_user.bookings.reject');

                        $html .= '<div class="mt-1">
                                    <button type="button" onclick="bookingAction(\'' . $confirmUrl . '\', ' . $id . ')" class="btn btn-success btn-xs me-1" title="' . __('general.confirm') . '">
                                        <i class="ti ti-check ti-xs"></i>
                                    </button>
                                    <button type="button" onclick="bookingAction(\'' . $rejectUrl . '\', ' . $id . ')" class="btn btn-danger btn-xs" title="' . __('general.reject') . '">
                                        <i class="ti ti-x ti-xs"></i>
                                    </button>
                                  </div>';
                    }

                    return $html;
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('product_payment', function ($row) {
                try {
                    $types = [];
                    foreach ($row->buyProducts ?? [] as $buyProduct) {
                        if (!empty($buyProduct->payment_type) && !in_array($buyProduct->payment_type, $types, true)) {
                            $types[] = $buyProduct->payment_type;
                        }
                    }
                    return empty($types) ? '-' : implode(', ', $types);
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('coupon_payment', function ($row) {
                try {
                    $types = [];
                    foreach ($this->collectCouponDetails($row) as $coupon) {
                        if (!empty($coupon['type']) && $coupon['type'] !== '-' && !in_array($coupon['type'], $types, true)) {
                            $types[] = $coupon['type'];
                        }
                    }
                    return empty($types) ? '-' : implode(', ', $types);
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('payment_methods', function ($row) {
                try {
                    $methods = [];
                    foreach ($row->bookings ?? [] as $booking) {
                        if (($booking->payment_type ?? null) === 'multiple' && !empty($booking->payment_methods)) {
                            foreach ($booking->payment_methods as $method) {
                                $name = $method['method'] ?? null;
                                if ($name && !in_array($name, $methods, true)) {
                                    $methods[] = $name;
                                }
                            }
                        } elseif (!empty($booking->payment_type) && !in_array($booking->payment_type, $methods, true)) {
                            $methods[] = $booking->payment_type;
                        }
                    }
                    foreach ($row->buyProducts ?? [] as $buyProduct) {
                        if (!empty($buyProduct->payment_type) && !in_array($buyProduct->payment_type, $methods, true)) {
                            $methods[] = $buyProduct->payment_type;
                        }
                    }
                    foreach ($this->collectCouponDetails($row) as $coupon) {
                        if (!empty($coupon['type']) && $coupon['type'] !== '-' && !in_array($coupon['type'], $methods, true)) {
                            $methods[] = $coupon['type'];
                        }
                    }
                    return empty($methods) ? '-' : implode(', ', $methods);
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->editColumn('worker.name', function ($row) {
                try {
                    return \App\Helpers\MyHelper::truncateWithReadMore($row->worker?->name ?? '-');
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('booking_employees', function ($row) {
                try {
                    $employees = [];
                    foreach ($row->bookings ?? [] as $booking) {
                        foreach ($booking->details ?? [] as $detail) {
                            $workerName = $detail->worker->name ?? null;
                            if ($workerName && !in_array($workerName, $employees, true)) {
                                $employees[] = $workerName;
                            }
                        }
                    }
                    return empty($employees) ? '-' : implode('<br>', $employees);
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->addColumn('product_employees', function ($row) {
                try {
                    $employees = [];
                    foreach ($row->buyProducts ?? [] as $buyProduct) {
                        if ($buyProduct->sales_worker && $buyProduct->sales_worker->name) {
                            $salesWorkerName = $buyProduct->sales_worker->name . ' (' . __('field.sales_worker') . ')';
                            if (!in_array($salesWorkerName, $employees, true)) {
                                $employees[] = $salesWorkerName;
                            }
                        }
                        if ($buyProduct->worker && $buyProduct->worker->name) {
                            $workerName = $buyProduct->worker->name . ' (' . __('field.worker') . ')';
                            if (!in_array($workerName, $employees, true)) {
                                $employees[] = $workerName;
                            }
                        }
                    }
                    return empty($employees) ? '-' : implode('<br>', $employees);
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->editColumn('client.name', function ($row) {
                try {
                    return \App\Helpers\MyHelper::truncateWithReadMore($row->client?->name ?? __('general.walk_in'));
                } catch (\Throwable $e) {
                    return __('general.walk_in');
                }
            })
            ->editColumn('tip', function ($row) {
                try {
                    return $row->tip > 0 ? number_format((float) $row->tip, 2) . ' ' . trim(get_currency()) : '-';
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->editColumn('total', function ($row) {
                try {
                    return number_format((float) $row->total, 2) . ' ' . trim(get_currency());
                } catch (\Throwable $e) {
                    return '-';
                }
            })
            ->rawColumns(['status', 'worker.name', 'client.name', 'services', 'products', 'packages', 'coupons', 'booking_employees', 'product_employees', 'booking_source', 'action'], true)
            ->setRowId('id');
    }

    public function query(Sale $model): QueryBuilder
    {
        $user = auth('center_user')->user();
        $branchId = $user->branch_id ?? null;

        // Do not eager-load saleItems.itemable — invalid morph types / soft-deletes can 500 the Ajax request.
        $query = $model->query()->with([
            'worker',
            'client',
            'branch.translation',
            'saleItems',
            'bookings' => function ($q) {
                $q->withTrashed()->with([
                    'details.service.translation',
                    'details.worker',
                ]);
            },
            'buyProducts.details.product.translation',
            'buyProducts.sales_worker',
            'buyProducts.worker',
        ])->withTrashed();

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $query->when(request()->filled('branch_id'), function ($query) {
            $query->where('branch_id', request()->input('branch_id'));
        });

        $source = request()->input('booking_source');
        if ($source === 'inside_booking' || $source === 'outside_booking') {
            $query->whereHas('bookings.details', function ($q) use ($source) {
                if ($source === 'inside_booking') {
                    $q->where(function ($inner) {
                        $inner->where('booking_source', 'inside_booking')
                            ->orWhereNull('booking_source')
                            ->orWhere('booking_source', '');
                    });
                } else {
                    $q->where('booking_source', 'outside_booking');
                }
            });
        }

        return $query->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        $initCompleteJs = <<<'JS'
function () {
    var api = this.api();
    var $filter = $("#booking_source_filter");
    $filter.off("change.lzSales").on("change.lzSales", function () {
        api.ajax.reload();
    });
    // After ColVis toggle, force columns to actually reflow/show
    api.on('column-visibility.dt', function () {
        setTimeout(function () {
            api.columns.adjust();
            api.draw(false);
        }, 0);
    });
}
JS;

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    var $f = $("#booking_source_filter");
                    d.booking_source = $f.length ? ($f.val() || "") : "";
                }',
            ])
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
                'className' => 'dt-center lz-actions-col noVis',
                'title' => __('general.actions'),
            ])
            ->buttons([
                [
                    'extend' => 'colvis',
                    'className' => $buttonClass . ' btn-luzori-beige',
                    'text' => __('general.column_visibility'),
                    'columns' => ':not(.noVis)',
                    'attr' => [
                        'id' => 'lz-sales-colvis',
                    ],
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
            ->addTableClass('table table-hover lz-sales-dt')
            ->initComplete($initCompleteJs)
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == "ar" ?
            asset('js/lang/ar_custom.json') :
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    protected function jsonB64(array $data): string
    {
        $json = json_encode($data);
        if ($json === false) {
            $json = '[]';
        }

        return base64_encode($json);
    }

    protected function collectBookingDetails($row): array
    {
        $currency = trim((string) get_currency());
        $details = [];

        foreach ($row->bookings ?? [] as $booking) {
            foreach ($booking->details ?? [] as $detail) {
                $details[] = [
                    'service' => (string) ($detail->service?->translation?->name ?? $detail->service?->name ?? 'N/A'),
                    'worker' => (string) ($detail->worker?->name ?? '-'),
                    'date' => (string) ($detail->_date ?? $booking->booking_date ?? '-'),
                    'time' => (string) (($detail->from_time ?? '') . '-' . ($detail->to_time ?? '')),
                    'price' => number_format((float) ($detail->price ?? 0), 2) . ' ' . $currency,
                ];
            }
        }

        return $details;
    }

    protected function collectProductDetails($row): array
    {
        $currency = trim((string) get_currency());
        $details = [];

        foreach ($row->buyProducts ?? [] as $buyProduct) {
            foreach ($buyProduct->details ?? [] as $detail) {
                $details[] = [
                    'product' => (string) ($detail->product?->translation?->name ?? $detail->product?->name ?? 'N/A'),
                    'price' => number_format((float) ($detail->price ?? 0), 2) . ' ' . $currency,
                ];
            }
        }

        return $details;
    }

    protected function collectPackageDetails($row): array
    {
        $currency = trim((string) get_currency());
        $details = [];
        $items = collect($row->saleItems ?? [])->where('item_type', 'user_package')->values();

        if ($items->isEmpty()) {
            return [];
        }

        $ids = $items->pluck('itemable_id')->filter()->unique()->all();
        $fallback = !empty($ids)
            ? \App\Models\UserPackage::with(['package'])
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id')
            : collect();

        foreach ($items as $saleItem) {
            $userPackage = $fallback->get($saleItem->itemable_id);
            $packageName = $userPackage?->package?->name
                ?? $userPackage?->package?->translation?->name
                ?? null;

            $details[] = [
                'package' => (string) ($packageName ?: (__('locale.package') . ' #' . ($saleItem->itemable_id ?? ''))),
                'price' => number_format((float) ($userPackage?->price ?? $saleItem->subtotal ?? $saleItem->price ?? 0), 2) . ' ' . $currency,
            ];
        }

        return $details;
    }

    protected function collectCouponDetails($row): array
    {
        $currency = trim((string) get_currency());
        $details = [];
        $items = collect($row->saleItems ?? [])->where('item_type', 'user_wallet')->values();

        if ($items->isEmpty()) {
            return [];
        }

        $ids = $items->pluck('itemable_id')->filter()->unique()->all();
        $fallback = !empty($ids)
            ? \App\Models\UserWallet::withTrashed()
                ->with(['wallet', 'user'])
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id')
            : collect();

        foreach ($items as $saleItem) {
            $userWallet = $fallback->get($saleItem->itemable_id);
            $details[] = [
                'code' => (string) ($userWallet?->wallet?->code ?? ('#' . ($saleItem->itemable_id ?? ''))),
                'amount' => number_format((float) ($userWallet?->amount ?? $saleItem->subtotal ?? $saleItem->price ?? 0), 2) . ' ' . $currency,
                'type' => (string) ($userWallet?->wallet_type ?? '-'),
                'user' => (string) ($userWallet?->user?->name ?? '-'),
            ];
        }

        return $details;
    }

    /**
     * Base64 JSON for the right-side drawer (safe inside DataTables Ajax HTML).
     */
    protected function salePanelB64($row): string
    {
        $saleId = (int) $row->id;
        if (isset($this->salePanelCache[$saleId])) {
            return $this->salePanelCache[$saleId];
        }

        $currency = trim((string) get_currency());
        $bookings = [];
        $products = [];
        $packages = [];
        $coupons = [];
        $notes = [];
        $paymentMethods = [];
        $statusLabel = '-';
        $statusTone = 'secondary';

        try {
            $bookings = $this->collectBookingDetails($row);
        } catch (\Throwable $e) {
            $bookings = [];
        }

        try {
            $products = $this->collectProductDetails($row);
        } catch (\Throwable $e) {
            $products = [];
        }

        try {
            $packages = $this->collectPackageDetails($row);
        } catch (\Throwable $e) {
            $packages = [];
        }

        try {
            $coupons = $this->collectCouponDetails($row);
        } catch (\Throwable $e) {
            $coupons = [];
        }

        try {
            foreach ($row->bookings ?? [] as $booking) {
                if (!empty($booking->notes)) {
                    $notes[] = trim((string) $booking->notes);
                }
                if (($booking->payment_type ?? null) === 'multiple' && is_array($booking->payment_methods ?? null)) {
                    foreach ($booking->payment_methods as $method) {
                        $name = is_array($method) ? ($method['method'] ?? null) : null;
                        if ($name && !in_array($name, $paymentMethods, true)) {
                            $paymentMethods[] = (string) $name;
                        }
                    }
                } elseif (!empty($booking->payment_type) && !in_array($booking->payment_type, $paymentMethods, true)) {
                    $paymentMethods[] = (string) $booking->payment_type;
                }
            }
            foreach ($row->buyProducts ?? [] as $buyProduct) {
                if (!empty($buyProduct->payment_type) && !in_array($buyProduct->payment_type, $paymentMethods, true)) {
                    $paymentMethods[] = (string) $buyProduct->payment_type;
                }
            }
            foreach ($coupons as $coupon) {
                if (!empty($coupon['type']) && $coupon['type'] !== '-' && !in_array($coupon['type'], $paymentMethods, true)) {
                    $paymentMethods[] = (string) $coupon['type'];
                }
            }
        } catch (\Throwable $e) {
            // keep partial payment/notes
        }

        try {
            $booking = optional($row->bookings)->first();
            if ($booking) {
                $firstDetail = optional($booking->details)->first();
                $status = $firstDetail?->status ?? 'confirmed';
                $source = $firstDetail?->booking_source ?? 'inside_booking';
                if ($source !== 'outside_booking') {
                    $deletedStatus = $booking->deleted_at ? 'cancelled' : 'confirmed';
                    $statusLabel = (string) __('field.booking_status_' . $deletedStatus);
                    $statusTone = $booking->deleted_at ? 'danger' : 'success';
                } else {
                    $statusLabel = (string) __('field.booking_status_' . $status);
                    $statusTone = ['pending' => 'warning', 'confirmed' => 'success', 'rejected' => 'danger'][$status] ?? 'secondary';
                }
            }
        } catch (\Throwable $e) {
            // keep defaults
        }

        $showUrl = url('/center_user/sales/show/' . $saleId);
        try {
            $showUrl = route('center_user.sales.show', $saleId);
        } catch (\Throwable $e) {
            // keep url fallback
        }

        $tipAmount = (float) ($row->tip ?? 0);
        $payload = [
            'id' => $saleId,
            'ref' => '#' . $saleId,
            'show_url' => $showUrl,
            'branch' => (string) ($row->branch?->translation?->name ?? '-'),
            'client' => (string) ($row->client?->name ?? __('general.walk_in')),
            'phone' => (string) ($row->client?->full_phone ?? $row->client?->phone ?? '-'),
            'payment' => empty($paymentMethods) ? '-' : implode(', ', $paymentMethods),
            'worker' => (string) ($row->worker?->name ?? '-'),
            'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-',
            'status_label' => $statusLabel,
            'status_tone' => $statusTone,
            'subtotal' => number_format((float) ($row->subtotal ?? 0), 2) . ' ' . $currency,
            'tax' => number_format((float) ($row->tax ?? 0), 2) . ' ' . $currency,
            'tip' => $tipAmount > 0 ? number_format($tipAmount, 2) . ' ' . $currency : '-',
            'total' => number_format((float) ($row->total ?? 0), 2) . ' ' . $currency,
            'bookings' => $bookings,
            'products' => $products,
            'packages' => $packages,
            'coupons' => $coupons,
            'notes' => implode("\n", array_values(array_unique(array_filter($notes)))),
        ];

        $json = json_encode($payload);
        if ($json === false) {
            $json = json_encode([
                'id' => $saleId,
                'ref' => '#' . $saleId,
                'show_url' => $showUrl,
                'branch' => $payload['branch'],
                'client' => $payload['client'],
                'phone' => $payload['phone'],
                'payment' => $payload['payment'],
                'created_at' => $payload['created_at'],
                'status_label' => $payload['status_label'],
                'status_tone' => $payload['status_tone'],
                'total' => $payload['total'],
                'bookings' => [],
                'products' => [],
                'packages' => [],
                'coupons' => [],
                'notes' => '',
            ]) ?: '{}';
        }

        return $this->salePanelCache[$saleId] = base64_encode($json);
    }

    public function getColumns(): array
    {
        $user = auth('center_user')->user();
        $canDelete = $user && $user->can('DELETE_SALES', 'center_api');
        
        // Do NOT use Responsive class "none" here — it permanently hides columns even when ColVis marks them visible.
        // Use visible(false) for initially-hidden columns so Column dropdown can show them.
        $columns = [
            Column::make('id')->searchable(true)->title('#')->addClass('all'),
            Column::computed('branch.translation.name')->searchable(true)->title(__('field.branch'))->addClass('all'),
            Column::computed('services')->searchable(false)->title(__('field.services') . ' (' . __('locale.bookings') . ')')->addClass('all'),
            Column::computed('packages')->searchable(false)->title(__('field.packages') . ' (' . __('locale.packages') . ')')->addClass('all'),
            Column::computed('products')->searchable(false)->title(__('locale.products'))->addClass('all'),
            Column::computed('coupons')->searchable(false)->title(__('field.coupons'))->addClass('all'),
            Column::computed('client.name')->searchable(true)->title(__('field.client'))->addClass('all'),
            Column::make('client_mobile')->searchable(true)->title(__('field.phone'))->addClass('all'),
            Column::computed('payment_methods')->searchable(false)->title(__('field.payment_methods') ?? 'Payment Methods')->visible(false),
            Column::computed('booking_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('locale.bookings') . ')')->visible(false),
            Column::computed('product_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('locale.products') . ')')->visible(false),
            Column::computed('coupon_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('field.coupons') . ')')->visible(false),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->visible(false),
            Column::computed('booking_source')->searchable(false)->title(__('api.booking_source') ?? 'Source')->visible(false),
            Column::computed('status')->searchable(false)->title(__('field.status'))->visible(false),
            Column::computed('booking_employees')->searchable(false)->title(__('field.employee') . ' (' . __('locale.bookings') . ')')->visible(false),
            Column::computed('product_employees')->searchable(false)->title(__('field.employee') . ' (' . __('locale.products') . ')')->visible(false),
            Column::computed('worker.name')->searchable(true)->title(__('field.worker'))->visible(false),
            Column::computed('tip')->searchable(false)->title(__('field.tip'))->visible(false),
            Column::computed('total')->searchable(false)->title(__('field.total'))->addClass('all'),
        ];
        
        if ($canDelete) {
            // Removed duplicate status column
        }
        
        return $columns;
    }

    protected function filename(): string
    {   
        return $this->plural . '_' . date('YmdHis');
    }
}



