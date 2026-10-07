<?php

namespace App\Services;

use App\Models\Center;
use App\Models\GlobalProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GlobalProductService
{
    public function query(): Builder
    {
        return GlobalProduct::withTrashed()
            ->with('center')
            ->orderByDesc('global_products.id');
    }

    public function centers(): Collection
    {
        return Center::query()
            ->orderBy('name')
            ->get(['id', 'name', 'database' ,'domain']);
    }

    public function productsForCenter(Center $center, string $search = ''): Collection
    {
        return $this->tenantProducts($center, null, $search, 500);
    }

    public function productDetailsForCenter(Center $center, int $productId): ?array
    {
        return $this->tenantProducts($center, [$productId], '', 1)->first();
    }

    public function add(Center $center, int $productId, int $sortOrder = 0): GlobalProduct
    {
        $product = $this->productForCenter($center, $productId);

        return DB::connection('central')->transaction(function () use ($center, $product, $sortOrder) {
            $globalProduct = GlobalProduct::withTrashed()
                ->where('center_id', $center->id)
                ->where('product_id', $product['id'])
                ->first();

            if (!$globalProduct) {
                return GlobalProduct::create([
                    'center_id' => $center->id,
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'sort_order' => $sortOrder,
                ]);
            }

            $globalProduct->fill([
                'name' => $product['name'],
                'sort_order' => $sortOrder,
            ]);
            if ($globalProduct->trashed()) {
                $globalProduct->restore();
            } else {
                $globalProduct->save();
            }

            return $globalProduct->fresh('center');
        });
    }

    public function publicProducts(?int $centerId, int $perPage = 20): LengthAwarePaginator
    {
        $products = GlobalProduct::query()
            ->with(['center:id,name,domain,database'])  
            ->whereHas('center', function ($query) use ($centerId) {
                $query->where('status', 'approve')
                    ->where(function ($expiry) {
                        $expiry->whereNull('expire_date')->orWhere('expire_date', '>', now());
                    })
                    ->when($centerId, fn ($centerQuery) => $centerQuery->whereKey($centerId));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);

        return $this->attachTenantProductDetails($products);
    }

    public function publicProductsForCenter(Center $center, int $perPage = 20): LengthAwarePaginator
    {
        if (!$this->isPublicCenter($center)) {
            return GlobalProduct::query()->whereRaw('1 = 0')->paginate($perPage);
        }

        $products = GlobalProduct::query()
            ->with(['center:id,name,domain,database'])  
            ->where('center_id', $center->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);

        return $this->attachTenantProductDetails($products);
    }

    public function publicProductForCenter(Center $center, int $productId): ?array
    {
        if (!$this->isPublicCenter($center)) {
            return null;
        }

        $globalProduct = GlobalProduct::query()
            ->with(['center:id,name,domain'])
            ->where('center_id', $center->id)
            ->where('product_id', $productId)
            ->first();

        if (!$globalProduct) {
            return null;
        }

        $details = $this->productDetailsForCenter($center, $productId)
            ?? $this->cachedProductDetails($globalProduct);

        return $this->serializeGlobalProduct($globalProduct, $details);
    }

    private function attachTenantProductDetails(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $rows = $paginator->getCollection();
        $detailsByCenter = [];

        foreach ($rows->groupBy('center_id') as $centerId => $centerRows) {
            $center = $centerRows->first()->center;
            if (!$center) {
                continue;
            }

            $detailsByCenter[$centerId] = $this->tenantProducts(
                $center,
                $centerRows->pluck('product_id')->map(fn ($id) => (int) $id)->all(),
                '',
                null
            )->keyBy('id');
        }

        $detailedRows = $rows->map(function (GlobalProduct $globalProduct) use ($detailsByCenter) {
            $details = $detailsByCenter[$globalProduct->center_id][$globalProduct->product_id] ?? null;
            $details ??= $this->cachedProductDetails($globalProduct);

            return $this->serializeGlobalProduct($globalProduct, $details);
        })->values();

        $paginator->setCollection($detailedRows);
        return $paginator;
    }

    private function serializeGlobalProduct(GlobalProduct $globalProduct, array $details): array
    {
        return [
            'id' => (int) $globalProduct->id,
            'center_id' => (int) $globalProduct->center_id,
            'center_name' => $globalProduct->center?->name,
            'center_domain' => $globalProduct->center?->domain,
            'product_id' => (int) $globalProduct->product_id,
            'name' => $details['name'] ?? $globalProduct->name,
            'sort_order' => (int) $globalProduct->sort_order,
            'product' => $details,
        ];
    }

    private function cachedProductDetails(GlobalProduct $globalProduct): array
    {
        return [
            'id' => (int) $globalProduct->product_id,
            'name' => (string) ($globalProduct->name ?? ''),
            'details_available' => false,
        ];
    }

    private function isPublicCenter(Center $center): bool
    {
        return $center->status === 'approve'
            && (!$center->expire_date || $center->expire_date->isFuture());
    }

    private function tenantProducts(Center $center, ?array $productIds, string $search, ?int $limit): Collection
    {
        if (!$center->database) {
            Log::warning('Global product tenant lookup skipped: center database is not configured.', [
                'center_id' => $center->id,
            ]);
            return collect();
        }

        try {
            return $this->withTenantDatabase($center, function () use ($center, $productIds, $search, $limit) {
                $query = Product::on('tenant')
                    ->with(['translation', 'brand', 'category', 'primarySku', 'skus', 'media']);

                if ($productIds !== null) {
                    $query->whereIn('id', $productIds);
                }

                if ($search !== '') {
                    $query->where(function ($products) use ($search) {
                        $products->where('barcode', 'like', '%' . $search . '%')
                            ->orWhereHas('translation', function ($translations) use ($search) {
                                $translations->where('name', 'like', '%' . $search . '%');
                            });
                        });
                }

                if ($limit !== null) {
                    $query->limit($limit);
                }

                return $query->orderBy('id')
                    ->get([
                        'id',
                        'barcode',
                        'brand_id',
                        'category_id',
                        'short_description',
                        'measure_unit',
                        'measure_amount',
                        'supply_price',
                        'retail_price',
                        'markup',
                        'allow_retail_sales',
                        'track_stock',
                    ])
                    ->map(fn (Product $product) => $this->formatTenantProduct($product))
                    ->values();
            });
        } catch (Throwable $exception) {
            Log::warning('Global product tenant lookup failed.', [
                'center_id' => $center->id,
                'database' => $center->database,
                'error' => $exception->getMessage(),
            ]);
            return collect();
        }
    }

    private function formatTenantProduct(Product $product): array
    {
        return [
            'id' => (int) $product->id,
            'details_available' => true,
            'name' => (string) ($product->name ?? ''),
            'description' => (string) ($product->text ?: $product->short_description ?: ''),
            'barcode' => $product->barcode,
            'brand' => $product->brand?->name,
            'category' => $product->category?->name,
            'measure_unit' => $product->measure_unit,
            'measure_amount' => (float) ($product->measure_amount ?? 0),
            'supply_price' => (float) ($product->supply_price ?? 0),
            'retail_price' => (float) ($product->retail_price ?? 0),
            'markup' => (float) ($product->markup ?? 0),
            'allow_retail_sales' => (bool) $product->allow_retail_sales,
            'track_stock' => (bool) $product->track_stock,
            'primary_sku' => $product->primarySku ? [
                'id' => (int) $product->primarySku->id,
                'sku' => $product->primarySku->sku,
                'type' => $product->primarySku->type,
            ] : null,
            'skus' => $product->skus->map(fn ($sku) => [
                'id' => (int) $sku->id,
                'sku' => $sku->sku,
                'type' => $sku->type,
            ])->values()->all(),
            'image_url' => $product->getFirstMediaUrl('Product') ?: null,
        ];
    }

    private function productForCenter(Center $center, int $productId): array
    {
        $product = $this->productDetailsForCenter($center, $productId);

        if (!$product) {
            throw new RuntimeException('The selected product does not exist for this center.');
        }

        return $product;
    }

    private function withTenantDatabase(Center $center, callable $callback): mixed
    {
        if (!$center->database) {
            throw new RuntimeException('The selected center has no product database configured.');
        }

        $previousDatabase = Config::get('database.connections.tenant.database');
        Config::set('database.connections.tenant.database', $center->database);
        DB::purge('tenant');

        try {
            return $callback();
        } finally {
            DB::purge('tenant');
            Config::set('database.connections.tenant.database', $previousDatabase);
        }
    }
}
