<?php

namespace App\Http\Controllers\AppAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private CartService $cartService)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'center_id' => 'nullable|integer|exists:central.centers,id',
            'item_type' => 'nullable|string|in:product,booking',
        ]);

        $items = $this->cartService->listForUser(
            $request->user(),
            isset($validated['center_id']) ? (int) $validated['center_id'] : null,
            $validated['item_type'] ?? null
        );

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, [
            'items' => CartItemResource::collection($items),
            'count' => $items->count(),
            'subtotal' => round((float) $items->sum('total_price'), 2),
        ]);
    }

    public function addProduct(Request $request)
    {
        $validated = $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'branch_id' => 'required|integer|min:1',
            'product_id' => 'required|integer|min:1',
            'qty' => 'required|integer|min:1',
        ]);

        try {
            $user = $request->user();
            $validated['_existing_qty'] = $this->cartService->existingProductQty(
                $user,
                (int) $validated['center_id'],
                (int) $validated['branch_id'],
                (int) $validated['product_id']
            );

            $item = $this->cartService->addProduct($user, $validated);

            return MyHelper::responseJSON(
                __('api.addSuccessfully'),
                Response::HTTP_CREATED,
                CartItemResource::make($item)
            );
        } catch (RuntimeException $e) {
            return MyHelper::responseJSON($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('AppAPI Cart addProduct: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateQty(Request $request, int $id)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:1',
        ]);

        try {
            $item = $this->cartService->updateProductQty($request->user(), $id, (int) $validated['qty']);

            return MyHelper::responseJSON(
                __('api.doneSuccessfully'),
                Response::HTTP_OK,
                CartItemResource::make($item)
            );
        } catch (RuntimeException $e) {
            return MyHelper::responseJSON($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('AppAPI Cart updateQty: ' . $e->getMessage());
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = $this->cartService->remove($request->user(), $id);

        if (!$deleted) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK);
    }

    public function clear(Request $request)
    {
        $validated = $request->validate([
            'center_id' => 'nullable|integer|exists:central.centers,id',
            'branch_id' => 'nullable|integer|min:1',
            'item_type' => 'nullable|string|in:product,booking',
        ]);

        $deleted = $this->cartService->clear(
            $request->user(),
            isset($validated['center_id']) ? (int) $validated['center_id'] : null,
            isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            $validated['item_type'] ?? null
        );

        return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK, [
            'deleted' => $deleted,
        ]);
    }

    /**
     * Build payload for POST app_api/buyproducts/add from cart product lines.
     */
    public function checkoutPayload(Request $request)
    {
        $validated = $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'branch_id' => 'required|integer|min:1',
        ]);

        $payload = $this->cartService->checkoutPayloadForBuyProducts(
            $request->user(),
            (int) $validated['center_id'],
            (int) $validated['branch_id']
        );

        if (empty($payload['products'])) {
            return MyHelper::responseJSON(__('api.noDataFound'), Response::HTTP_NOT_FOUND);
        }

        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $payload);
    }
}
