<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\ExpensesProviderDataTable;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExpensesController extends Controller
{
    /**
     * Display the expenses index page
     */
    public function index(Request $request)
    {
        $title = __('locale.expenses');
        $item = null; // For create mode
        $requestUrl = route('center_user.expenses.updateOrCreate');
        
        // Get data for dropdowns
        $branches = Branch::all();
        $suppliers = Supplier::all();
        $workers = Worker::all();
        
        return view('CenterUser.SubViews.Expenses.index', compact('title', 'item', 'requestUrl', 'branches', 'suppliers', 'workers'));
    }

    /**
     * Show the form for creating a new expense or editing an existing one
     */
    public function create(Request $request)
    {
        $title = __('locale.expenses');
        $item = null;
        $requestUrl = route('center_user.expenses.updateOrCreate');
        
        // Check if this is an edit request
        if ($request->has('id') && $request->id) {
            $item = Expense::with(['branch', 'supplier'])->findOrFail($request->id);
            $title = __('general.edit') . ' ' . __('locale.expenses');
        } else {
            $title = __('general.add_new') . ' ' . __('locale.expenses');
        }
        
        // Get data for dropdowns
        $branches = Branch::all();
        $suppliers = Supplier::all();
        $workers = Worker::all();
        
        return view('CenterUser.SubViews.Expenses.index', compact('title', 'item', 'requestUrl', 'branches', 'suppliers', 'workers'));
    }

    /**
     * Store or update an expense
     */
    public function updateOrCreate(Request $request)
    {
        // Handle expense name validation based on salary checkbox
        $expenseNameRule = 'nullable|string|max:255';
        if (!$request->has('is_salary') || !$request->boolean('is_salary')) {
            $expenseNameRule = 'required|string|max:255';
        }

        // Handle supplier_id and payee validation based on fast expense checkbox
        $supplierIdRule = 'nullable|exists:suppliers,id';
        $payeeRule = 'nullable|string|max:255';
        
        // If fast expense is NOT checked, make them required
        if (!$request->has('is_fast_expense') || !$request->boolean('is_fast_expense')) {
            // If not fast expense and not salary, require supplier
            if (!$request->has('is_salary') || !$request->boolean('is_salary')) {
                $supplierIdRule = 'required|exists:suppliers,id';
            }
            $payeeRule = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => $supplierIdRule,
            'expense_name' => $expenseNameRule,
            'payee' => $payeeRule,
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_salary' => 'nullable|boolean',
            'is_fast_expense' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->only(['branch_id', 'supplier_id', 'payee', 'amount', 'start_date', 'end_date', 'date', 'notes']);
            
            // Handle expense name logic
            if ($request->boolean('is_salary')) {
                $data['expense_name'] = 'Salary';
                $data['supplier_id'] = null; // No supplier for salary expenses
            } else {
                $data['expense_name'] = $request->expense_name;
                // supplier_id is already included from the request
            }
            
            // Handle fast expense - if checked, payee can be empty
            if ($request->boolean('is_fast_expense')) {
                if (empty($data['payee'])) {
                    $data['payee'] = null;
                }
                if (empty($data['supplier_id'])) {
                    $data['supplier_id'] = null;
                }
            }

            // Handle receipt image upload
            if ($request->hasFile('receipt_image')) {
                $file = $request->file('receipt_image');
                $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('expenses/receipts', $filename, 'public');
                $data['receipt_image'] = $path;
            }

            if ($request->id) {
                // Update existing expense
                $expense = Expense::findOrFail($request->id);
                $expense->update($data);
                $message = __('general.updated_successfully');
            } else {
                // Create new expense
                $expense = Expense::create($data);
                $message = __('general.created_successfully');
            }

            return redirect()->route('center_user.expenses.providers')
                ->with('success', $message);

        } catch (\Exception $e) { 
            return redirect()->back()
                ->with('error', __('general.error_occurred') . ': ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the providers page
     */
    public function providers(ExpensesProviderDataTable $dataTable)
    {
        $title = __('locale.expenses_providers');
        
        return $dataTable->render("CenterUser.SubViews.core-table", compact('title'));
    }

    /**
     * Update or create a provider
     */
    public function updateProvider(Request $request)
    {
        return $this->updateOrCreate($request);
    }
}
