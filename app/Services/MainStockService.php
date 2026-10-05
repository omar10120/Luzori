<?php

namespace App\Services;

use App\Models\MainStock;
use Illuminate\Database\Eloquent\Builder;

class MainStockService
{
    public function query(): Builder
    {
        return MainStock::query()
            ->with(['product.translation', 'sku'])
            ->orderBy('product_id')
            ->orderBy('sku_id');
    }
}
