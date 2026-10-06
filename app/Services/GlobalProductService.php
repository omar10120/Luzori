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
use RuntimeException;

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
        return $this->withTenantDatabase($center, function () use ($search) {
            return Product::on('tenant')
                ->with('translation')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($products) use ($search) {
                        $products->where('barcode', 'like', '%' . $search . '%')
                            ->orWhereHas('translation', function ($translations) use ($search) {
                                $translations->where('name', 'like', '%' . $search . '%');
                            });
                    });
                })
                ->orderBy('id')
                ->limit(500)
                ->get(['id', 'barcode'])
                ->map(fn (Product $product) => [
                    'id' => (int) $product->id,
                    'name' => (string) ($product->name ?? ''),
                    'barcode' => $product->barcode,
                ])
                ->values();
        });
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
        return GlobalProduct::query()
            ->with(['center:id,name,domain'])
            ->whereHas('center', function ($query) use ($centerId) {
                $query->where('status', 'approve')
                    ->where(function ($expiry) {
                        $expiry->whereNull('expire_date')
                            ->orWhere('expire_date', '>', now());
                    })
                    ->when($centerId, fn ($centerQuery) => $centerQuery->whereKey($centerId));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage);
    }

    private function productForCenter(Center $center, int $productId): array
    {
        $product = $this->withTenantDatabase($center, function () use ($productId) {
            return Product::on('tenant')
                ->with('translation')
                ->whereKey($productId)
                ->first(['id', 'barcode']);
        });

        if (!$product) {
            throw new RuntimeException('The selected product does not exist for this center.');
        }

        return [
            'id' => (int) $product->id,
            'name' => (string) ($product->name ?? ''),
            'barcode' => $product->barcode,
        ];
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
