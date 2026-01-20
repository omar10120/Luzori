<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CRUDService
{
    public function first($model, $relations = [], $withTrashed = 1)
    {
        $modelNameSpace = 'App\Models\\' . $model;
        return $withTrashed == 1 ? $modelNameSpace::withTrashed()->with($relations)->first() : $modelNameSpace::with($relations)->first();
    }

    public function all($model, $relations = [], $withTrashed = 1)
    {
        $modelNameSpace = 'App\Models\\' . $model;
        return $withTrashed == 1 ? $modelNameSpace::with($relations)->withTrashed()->get() : $modelNameSpace::with($relations)->get();
    }

    public function paginate($model, $relations = [], $withTrashed = 1)
    {
        $modelNameSpace = 'App\Models\\' . $model;
        return $withTrashed == 1 ? $modelNameSpace::with($relations)->withTrashed()->paginate(10) : $modelNameSpace::with($relations)->paginate(10);
    }

    public function find($model, $id, $relations = [], $withTrashed = 1)
    {
        $modelNameSpace = 'App\Models\\' . $model;
        return $withTrashed == 1 ? $modelNameSpace::withTrashed()->with($relations)->findOrFail($id) : $modelNameSpace::with($relations)->findOrFail($id);
    }

    function updateOrCreate($model, $request, $withTrashed = null)
    {
        DB::beginTransaction();
        $modelNameSpace = 'App\Models\\' . $model;
        if ($withTrashed) {
            $item = $modelNameSpace::withTrashed()->updateOrCreate(['id' => $request['id'] ?? null], $request);
        } else {
            $item = $modelNameSpace::updateOrCreate(['id' => $request['id'] ?? null], $request);
        }

        // Handle single image upload (skip for Product as it uses multi-image)
        if (isset($request['image']) && $model != 'Product') {
            $item->clearMediaCollection($model);
            $item->addMedia($request['image'])->toMediaCollection($model);
        }

        if ($model == 'Worker') {
            if (isset($request['services'])) {
                $item->services()->delete();
                foreach ($request['services'] as $service) {
                    $item->services()->create([
                        'service_id' => $service
                    ]);
                }
            }
        }

        if ($model == 'BuyProduct') {
            // Set created_by field for BuyProduct
            if (!isset($request['id']) || empty($request['id'])) {
                // This is a new record, set created_by
                $item->created_by = auth('center_user')->id() ?? auth('center_api')->id();
                $item->save();
            }
            
            foreach ($request['products'] as $product) {
                $product = Product::select(['id', 'retail_price', 'supply_price'])->find($product);
                
                // Use retail_price if available and > 0, otherwise use supply_price
                $price = ($product->retail_price && $product->retail_price > 0) 
                    ? $product->retail_price 
                    : ($product->supply_price ?? 0);
                
                $item->details()->create([
                    'product_id' => $product->id,
                    'price' => $price
                ]);
            }
        }

        if ($model == 'Package') {
            if (isset($request['paid_services'])) {
                $item->packageServicePaid()->delete();
                foreach ($request['paid_services'] as $service) {
                    $item->packageServicePaid()->create([
                        'service_id' => $service
                    ]);
                }
            }

            if (isset($request['free_services'])) {
                $item->PackageServiceFree()->delete();
                foreach ($request['free_services'] as $service) {
                    $item->PackageServiceFree()->create([
                        'service_id' => $service
                    ]);
                }
            }
        }

        if (isset($request['role'])) {
            $item->roles()->detach();
            $item->assignRole($request['role']);
        }

        DB::commit();
        return $item;
    }

    function delete($model, $id)
    {
        $modelNameSpace = 'App\Models\\' . $model;
        return $modelNameSpace::find($id)->delete();
    }
}
