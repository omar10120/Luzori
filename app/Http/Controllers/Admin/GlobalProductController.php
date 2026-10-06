<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\GlobalProductDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Services\GlobalProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RuntimeException;

class GlobalProductController extends Controller
{
    public function __construct(private GlobalProductService $globalProductService)
    {
    }

    public function index(GlobalProductDataTable $dataTable)
    {
        abort_unless(auth('admin')->user()->can('VIEW_GLOBALPRODUCTS'), 403);

        $title = __('locale.globalproducts');
        return $dataTable->render('Admin.SubViews.core-table', compact('title'));
    }

    public function create()
    {
        abort_unless(auth('admin')->user()->can('CREATE_GLOBALPRODUCTS'), 403);

        $centers = $this->globalProductService->centers();
        $title = __('general.add') . ' ' . __('locale.globalproducts');
        $menu = __('locale.globalproducts');
        $menu_link = route('admin.global-products.index');
        $productsUrl = route('admin.global-products.products', ['center' => '__CENTER__']);
        $storeUrl = route('admin.global-products.store');

        return view('Admin.SubViews.GlobalProducts.create', compact(
            'centers',
            'title',
            'menu',
            'menu_link',
            'productsUrl',
            'storeUrl'
        ));
    }

    public function products(Request $request, Center $center)
    {
        abort_unless(auth('admin')->user()->can('CREATE_GLOBALPRODUCTS'), 403);

        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        try {
            return response()->json([
                'data' => $this->globalProductService->productsForCenter($center, $validated['q'] ?? ''),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function store(Request $request)
    {
        abort_unless(auth('admin')->user()->can('CREATE_GLOBALPRODUCTS'), 403);

        $validated = $request->validate([
            'center_id' => 'required|integer|exists:central.centers,id',
            'product_id' => 'required|integer',
            'sort_order' => 'nullable|integer|min:0|max:2147483647',
        ]);

        $center = Center::findOrFail($validated['center_id']);

        try {
            $globalProduct = $this->globalProductService->add(
                $center,
                (int) $validated['product_id'],
                (int) ($validated['sort_order'] ?? 0)
            );
        } catch (RuntimeException $exception) {
            return MyHelper::responseJSON($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return MyHelper::responseJSON(
            'redirect_to_home',
            Response::HTTP_CREATED,
            ['id' => $globalProduct->id, 'redirect_url' => route('admin.global-products.index')]
        );
    }
}
