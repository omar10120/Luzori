<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\BuyProductResource;
use App\Http\Resources\PaginateDateResource;
use App\Http\Resources\PrintBuyProductResource;
use App\Models\BuyProduct;
use App\Models\Center;
use App\Models\User as TenantUser;
use App\Services\CRUDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BuyProductController extends Controller
{
    private CRUDService $crudService;
    private string $model = 'BuyProduct';

    public function __construct(CRUDService $crudService)
    {
        $this->crudService = $crudService;
    }

    public function all(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
        ]);

        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }

        try {
            $relations = ['worker', 'sales_worker', 'details' => function ($q) {
                $q->with(['product' => function ($q1) {
                    $q1->with(['translation']);
                }]);
            }];

            $items = $this->crudService->paginate($this->model, $relations, 0);
            if ($items) {
                $paginationData = PaginateDateResource::make($items);
                $items = BuyProductResource::collection($items);
                return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $items, $paginationData);
            }

            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            Log::error('AppAPI BuyProduct all: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function add(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
        ]);

        $appUser = auth('sanctum')->user();
        if (!$appUser) {
            return MyHelper::responseJSON('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }

        $validated = $request->validate([
            'products' => 'required|array',
            'products.*' => 'required|exists:products,id,deleted_at,NULL',
            'payment_type' => 'required|string|in:' . implode(',', get_payment_method_names()),
            'commission' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'sales_worker_id' => 'required|exists:workers,id,deleted_at,NULL',
            'worker_id' => 'nullable|exists:workers,id,deleted_at,NULL',
        ]);

        try {
            $tenantUser = $this->resolveTenantUser($appUser);

            $item = $this->crudService->updateOrCreate($this->model, $validated, true);
            if (!$item) {
                return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $item->created_by = $tenantUser->id;
            $item->save();

            $item->load(['worker', 'sales_worker', 'details.product.translation']);

            return MyHelper::responseJSON(
                __('api.addSuccessfully'),
                Response::HTTP_CREATED,
                BuyProductResource::make($item)
            );
        } catch (\Exception $e) {
            Log::error('AppAPI BuyProduct add: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'id' => 'required|integer',
        ]);

        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }

        $request->validate([
            'id' => 'required|exists:buy_products,id,deleted_at,NULL',
        ]);

        try {
            $item = $this->crudService->delete($this->model, $request->id);
            if ($item) {
                return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
            }

            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            Log::error('AppAPI BuyProduct delete: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function printInvoice(Request $request)
    {
        $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'id' => 'required|integer',
        ]);

        $switched = $this->switchToCenter((int) $request->center_id);
        if ($switched instanceof \Illuminate\Http\JsonResponse) {
            return $switched;
        }

        $request->validate([
            'id' => 'required|exists:buy_products,id,deleted_at,NULL',
        ]);

        try {
            $buyProduct = BuyProduct::with(['created_by_user', 'details' => function ($q) {
                $q->with(['product' => function ($q) {
                    $q->with(['translation']);
                }]);
            }])->findOrFail($request->id);

            return MyHelper::responseJSON(
                __('api.doneSuccessfully'),
                Response::HTTP_OK,
                PrintBuyProductResource::make($buyProduct)
            );
        } catch (\Exception $e) {
            Log::error('AppAPI BuyProduct printInvoice: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function switchToCenter(int $centerId)
    {
        $center = Center::find($centerId);
        if (!$center || !$center->database) {
            return MyHelper::responseJSON('Center not found or database missing', Response::HTTP_NOT_FOUND);
        }
        if ($center->expire_date && now()->gt($center->expire_date)) {
            return MyHelper::responseJSON(
                'انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.',
                Response::HTTP_BAD_REQUEST
            );
        }

        Config::set('database.connections.mysql.database', $center->database);
        DB::purge('mysql');
        DB::reconnect('mysql');

        return $center;
    }

    private function resolveTenantUser($appUser): TenantUser
    {
        $tenantUser = TenantUser::where('email', $appUser->email)
            ->orWhere('phone', $appUser->phone)
            ->first();

        if ($tenantUser) {
            return $tenantUser;
        }

        return TenantUser::create([
            'first_name' => $appUser->first_name,
            'last_name' => $appUser->last_name,
            'email' => $appUser->email,
            'phone' => $appUser->phone ?? '-',
            'country_code' => $appUser->country_code ?? '+',
            'wallet' => 0,
        ]);
    }
}
