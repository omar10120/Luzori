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
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $canDelete = auth('center_user')->user()?->can('DELETE_SALES', 'center_api');
                $options = [
                    'show' => true,
                    'print' => true,
                    'row_details' => false,
                    'sale_panel' => $this->salePanelAttr($this->salePanelPayload($item)),
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                if ($canDelete) {
                    $options['sale_otp_delete'] = true;
                }

                $hasBooking = $item->bookings->isNotEmpty()
                    || $item->saleItems->contains(fn ($si) => $si->item_type === 'booking');
                $hasTip = (float) ($item->tip ?? 0) > 0
                    || $item->bookings->flatMap->details->sum(fn ($d) => (float) ($d->tip ?? 0)) > 0;

                if ($hasBooking && !$hasTip) {
                    $options['add_tip'] = true;
                }

                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->editColumn('branch.translation.name', function ($row) {
                return $row->branch?->translation?->name ?? '-';
            })
            ->addColumn('services', function ($row) {
                $panel = $this->salePanelPayload($row);
                $count = count($panel['bookings'] ?? []);

                if ($count === 0) {
                    return '-';
                }

                $html = '<span class="lz-sale-badge is-booking">' . $count . ' ' . __('locale.bookings') . '</span>';
                $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-side-details" data-focus="bookings" data-sale-panel="' . $this->salePanelAttr($panel) . '">';
                $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                $html .= '</button>';

                return $html;
            })
            ->addColumn('products', function ($row) {
                $panel = $this->salePanelPayload($row);
                $count = count($panel['products'] ?? []);

                if ($count === 0) {
                    return '-';
                }

                $html = '<span class="lz-sale-badge is-product">' . $count . ' ' . __('locale.products') . '</span>';
                $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-side-details" data-focus="products" data-sale-panel="' . $this->salePanelAttr($panel) . '">';
                $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                $html .= '</button>';

                return $html;
            })
            ->addColumn('packages', function ($row) {
                $panel = $this->salePanelPayload($row);
                $count = count($panel['packages'] ?? []);

                if ($count === 0) {
                    return '-';
                }

                $packageLabel = $count === 1 ? __('locale.package') : __('locale.packages');
                $html = '<span class="lz-sale-badge is-package">' . $count . ' ' . $packageLabel . '</span>';
                $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-side-details" data-focus="packages" data-sale-panel="' . $this->salePanelAttr($panel) . '">';
                $html .= '<i class="ti ti-eye me-1"></i>' . __('general.view_details');
                $html .= '</button>';

                return $html;
            })
            ->addColumn('coupons', function ($row) {
                $panel = $this->salePanelPayload($row);
                $count = count($panel['coupons'] ?? []);

                if ($count === 0) {
                    return '-';
                }

                $html = '<span class="lz-sale-badge is-coupon">' . $count . ' ' . __('field.coupons') . '</span>';
                $html .= ' <button type="button" class="btn btn-sm lz-sale-detail-btn ms-1 view-sale-side-details" data-focus="coupons" data-sale-panel="' . $this->salePanelAttr($panel) . '">';
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
                $methods = [];
                
                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'booking' && $saleItem->itemable) {
                        $booking = $saleItem->itemable;
                        if ($booking->payment_type === 'multiple' && !empty($booking->payment_methods)) {
                            foreach ($booking->payment_methods as $method) {
                                $methodName = $method['method'] ?? 'N/A';
                                $amount = number_format($method['amount'] ?? 0, 2);
                                $methods[] = $methodName . " (" . $amount . ")";
                            }
                        } elseif ($booking->payment_type) {
                            if (!in_array($booking->payment_type, $methods, true)) {
                                $methods[] = $booking->payment_type;
                            }
                        }
                    }
                }

                return empty($methods) ? '-' : implode(', ', $methods);
            })
            ->addColumn('booking_source', function ($row) {
                $sources = [];
                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->item_type === 'booking' && $saleItem->itemable) {
                        $booking = $saleItem->itemable;
                        foreach ($booking->details as $detail) {
                            $source = $detail->booking_source ?? 'inside_booking';
                            if (!in_array($source, $sources, true)) {
                                $sources[] = $source;
                            }
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
            })
            ->addColumn('status', function ($row) {
                // Find all bookings associated with this sale
                $booking = $row->bookings->first();
                if (!$booking) {
                    return '-';
                }

                $firstDetail = $booking->details->first();
                $status = $firstDetail->status ?? 'confirmed';
                $source = $firstDetail->booking_source ?? 'inside_booking';

                // Handle inside bookings (just look at deleted_at)
                if ($source !== 'outside_booking') {
                    $deletedStatus = $booking->deleted_at ? 'cancelled' : 'confirmed';
                    $class = $booking->deleted_at ? 'bg-label-danger' : 'bg-label-success';
                    return '<span class="badge ' . $class . '">' . __('field.booking_status_' . $deletedStatus) . '</span>';
                }

                // Handle outside bookings (use the new status column)
                $map = [
                    'pending'   => 'bg-label-warning',
                    'confirmed' => 'bg-label-success',
                    'rejected'  => 'bg-label-danger',
                ];
                $class = $map[$status] ?? 'bg-label-secondary';
                $html = '<span class="badge ' . $class . '">' . __('field.booking_status_' . $status) . '</span>';

                // Add confirm/reject actions for pending outside bookings
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
            ->addColumn('payment_methods', function ($row) {
                $methods = [];
                
                foreach ($row->saleItems as $saleItem) {
                    if ($saleItem->itemable) {
                        $itemable = $saleItem->itemable;
                        
                        // Check for common payment_type field
                        $type = $itemable->payment_type ?? $itemable->wallet_type ?? null;
                        
                        if ($type === 'multiple' && isset($itemable->payment_methods) && !empty($itemable->payment_methods)) {
                            foreach ($itemable->payment_methods as $method) {
                                $name = $method['method'] ?? 'N/A';
                                if (!in_array($name, $methods, true)) {
                                    $methods[] = $name;
                                }
                            }
                        } elseif ($type && !in_array($type, $methods, true)) {
                            $methods[] = $type;
                        }
                    }
                }
                
                return empty($methods) ? '-' : implode(', ', $methods);
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
            ->rawColumns(['status', 'worker.name', 'client.name', 'services', 'products', 'packages', 'coupons', 'booking_employees', 'product_employees', 'booking_source', 'action'], true)
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
                        } elseif ($model instanceof \App\Models\UserPackage) {
                            $q->with(['package.translation']);
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

        $query->when(request()->has('booking_source'), function ($query) {
            $source = request()->input('booking_source');
            if ($source === 'inside_booking' || $source === 'outside_booking') {
                $query->whereHas('bookings.details', function ($q) use ($source) {
                    $q->where('booking_source', $source);
                });
            }
        });

        return $query->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.cart');

        $initCompleteJs = 'function () {';
        $initCompleteJs .= '$("#booking_source_filter").on("change", function() { window.LaravelDataTables["sales-table"].draw(); });';
        $initCompleteJs .= '}';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->ajax([
                'data' => 'function(d) {
                    d.booking_source = $("#booking_source_filter").val();
                }',
            ])
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
            ->initComplete($initCompleteJs)
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == "ar" ?
            asset('js/lang/ar_custom.json') :
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    /**
     * Compact sale summary for the right-side details drawer.
     * Prefer direct sale relations (bookings / buyProducts) — same data the table already eager-loads.
     */
    protected function salePanelPayload($row): array
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

        // Bookings via sale.bookings (reliable; already eager-loaded)
        foreach ($row->bookings ?? [] as $booking) {
            if (!empty($booking->notes)) {
                $notes[] = trim((string) $booking->notes);
            }

            $methods = $booking->payment_methods ?? null;
            if (($booking->payment_type ?? null) === 'multiple' && is_array($methods)) {
                foreach ($methods as $method) {
                    $name = is_array($method) ? ($method['method'] ?? null) : null;
                    if ($name && !in_array($name, $paymentMethods, true)) {
                        $paymentMethods[] = $name;
                    }
                }
            } elseif (!empty($booking->payment_type) && !in_array($booking->payment_type, $paymentMethods, true)) {
                $paymentMethods[] = $booking->payment_type;
            }

            foreach ($booking->details ?? [] as $detail) {
                $bookings[] = [
                    'service' => $detail->service?->translation?->name ?? $detail->service?->name ?? 'N/A',
                    'worker' => $detail->worker?->name ?? '-',
                    'date' => $detail->_date ?? $booking->booking_date ?? '-',
                    'time' => trim(($detail->from_time ?? '') . '-' . ($detail->to_time ?? ''), '-'),
                    'price' => number_format((float) ($detail->price ?? 0), 2) . ' ' . $currency,
                ];
            }
        }

        // Products via sale.buyProducts
        foreach ($row->buyProducts ?? [] as $buyProduct) {
            $type = $buyProduct->payment_type ?? null;
            if ($type && !in_array($type, $paymentMethods, true)) {
                $paymentMethods[] = $type;
            }
            foreach ($buyProduct->details ?? [] as $detail) {
                $products[] = [
                    'product' => $detail->product?->translation?->name ?? $detail->product?->name ?? 'N/A',
                    'price' => number_format((float) ($detail->price ?? 0), 2) . ' ' . $currency,
                ];
            }
        }

        // Packages / coupons still come from saleItems morph
        foreach ($row->saleItems ?? [] as $saleItem) {
            if (!$saleItem->itemable) {
                continue;
            }

            if ($saleItem->item_type === 'user_package') {
                $userPackage = $saleItem->itemable;
                $packages[] = [
                    'package' => $userPackage->package?->translation?->name ?? $userPackage->package?->name ?? 'N/A',
                    'price' => number_format((float) ($userPackage->price ?? 0), 2) . ' ' . $currency,
                ];
            } elseif ($saleItem->item_type === 'user_wallet') {
                $userWallet = $saleItem->itemable;
                $type = $userWallet->wallet_type ?? null;
                if ($type && !in_array($type, $paymentMethods, true)) {
                    $paymentMethods[] = $type;
                }
                $coupons[] = [
                    'code' => $userWallet->wallet?->code ?? '-',
                    'amount' => number_format((float) ($userWallet->amount ?? 0), 2) . ' ' . $currency,
                    'type' => $userWallet->wallet_type ?? '-',
                    'user' => $userWallet->user?->name ?? '-',
                ];
            } elseif ($saleItem->item_type === 'booking' && empty($bookings) && $saleItem->itemable) {
                // Fallback if bookings relation empty but saleItems present
                $booking = $saleItem->itemable;
                foreach ($booking->details ?? [] as $detail) {
                    $bookings[] = [
                        'service' => $detail->service?->translation?->name ?? $detail->service?->name ?? 'N/A',
                        'worker' => $detail->worker?->name ?? '-',
                        'date' => $detail->_date ?? $booking->booking_date ?? '-',
                        'time' => trim(($detail->from_time ?? '') . '-' . ($detail->to_time ?? ''), '-'),
                        'price' => number_format((float) ($detail->price ?? 0), 2) . ' ' . $currency,
                    ];
                }
            } elseif ($saleItem->item_type === 'buy_product' && empty($products) && $saleItem->itemable) {
                $buyProduct = $saleItem->itemable;
                foreach ($buyProduct->details ?? [] as $detail) {
                    $products[] = [
                        'product' => $detail->product?->translation?->name ?? $detail->product?->name ?? 'N/A',
                        'price' => number_format((float) ($detail->price ?? 0), 2) . ' ' . $currency,
                    ];
                }
            }
        }

        $statusLabel = '-';
        $statusTone = 'secondary';
        $booking = optional($row->bookings)->first();
        if ($booking) {
            $firstDetail = optional($booking->details)->first();
            $status = $firstDetail?->status ?? 'confirmed';
            $source = $firstDetail?->booking_source ?? 'inside_booking';

            if ($source !== 'outside_booking') {
                $deletedStatus = $booking->deleted_at ? 'cancelled' : 'confirmed';
                $statusLabel = __('field.booking_status_' . $deletedStatus);
                $statusTone = $booking->deleted_at ? 'danger' : 'success';
            } else {
                $statusLabel = __('field.booking_status_' . $status);
                $map = [
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'rejected' => 'danger',
                ];
                $statusTone = $map[$status] ?? 'secondary';
            }
        }

        $notes = array_values(array_unique(array_filter($notes)));

        $showUrl = url('/center_user/sales/show/' . $saleId);
        try {
            $showUrl = route('center_user.sales.show', $saleId);
        } catch (\Throwable $e) {
            // keep fallback url
        }

        $payload = [
            'id' => $saleId,
            'ref' => '#' . $saleId,
            'show_url' => $showUrl,
            'branch' => $row->branch?->translation?->name ?? '-',
            'client' => $row->client?->name ?? __('general.walk_in'),
            'phone' => $row->client?->full_phone ?? $row->client?->phone ?? '-',
            'payment' => empty($paymentMethods) ? '-' : implode(', ', $paymentMethods),
            'created_at' => $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-',
            'status_label' => $statusLabel,
            'status_tone' => $statusTone,
            'total' => number_format((float) ($row->total ?? 0), 2) . ' ' . $currency,
            'bookings' => $bookings,
            'products' => $products,
            'packages' => $packages,
            'coupons' => $coupons,
            'notes' => implode("\n", $notes),
        ];

        return $this->salePanelCache[$saleId] = $payload;
    }

    protected function salePanelAttr(array $panel): string
    {
        $flags = JSON_UNESCAPED_UNICODE;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }

        $json = json_encode($panel, $flags);
        if ($json === false) {
            $json = '{}';
        }

        return e($json);
    }

    public function getColumns(): array
    {
        $user = auth('center_user')->user();
        $canDelete = $user && $user->can('DELETE_SALES', 'center_api');
        
        $columns = [
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('dtr-control all'),
            Column::computed('branch.translation.name')->searchable(true)->title(__('field.branch'))->responsivePriority(2)->addClass('all'),
            Column::computed('services')->searchable(false)->title(__('field.services') . ' (' . __('locale.bookings') . ')')->responsivePriority(2)->addClass('all'),
            Column::computed('packages')->searchable(false)->title(__('field.packages') . ' (' . __('locale.packages') . ')')->responsivePriority(3)->addClass('all'),
            Column::computed('products')->searchable(false)->title(__('locale.products'))->responsivePriority(3)->addClass('all'),
            Column::computed('coupons')->searchable(false)->title(__('field.coupons'))->responsivePriority(3)->addClass('all'),
            Column::computed('client.name')->searchable(true)->title(__('field.client'))->responsivePriority(1)->addClass('all'),
            Column::make('client_mobile')->searchable(true)->title(__('field.phone'))->responsivePriority(2)->addClass('all'),
            Column::computed('payment_methods')->searchable(false)->title(__('field.payment_methods') ?? 'Payment Methods')->addClass('none'),
            Column::computed('booking_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('locale.bookings') . ')')->addClass('none'),
            Column::computed('product_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('locale.products') . ')')->addClass('none'),
            Column::computed('coupon_payment')->searchable(true)->title(__('field.payment_method') . ' (' . __('field.coupons') . ')')->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
            Column::computed('booking_source')->searchable(false)->title(__('api.booking_source') ?? 'Source')->addClass('none'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->addClass('none'),
            Column::computed('booking_employees')->searchable(false)->title(__('field.employee') . ' (' . __('locale.bookings') . ')')->addClass('none'),
            Column::computed('product_employees')->searchable(false)->title(__('field.employee') . ' (' . __('locale.products') . ')')->addClass('none'),
            Column::computed('worker.name')->searchable(true)->title(__('field.worker'))->addClass('none'),
            Column::computed('tip')->searchable(false)->title(__('field.tip'))->addClass('none'),
            Column::computed('total')->searchable(false)->title(__('field.total'))->responsivePriority(2)->addClass('all'),
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



