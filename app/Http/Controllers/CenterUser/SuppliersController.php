<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Datatables\CenterUser\SuppliersDataTable;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuppliersController extends Controller
{
    public function index(SuppliersDataTable $dataTable)
    {
        $title = __('locale.suppliers');
        return $dataTable->render('CenterUser.SubViews.core-table', compact('title'));
    }

    public function create(Request $request)
    {
        $id = $request->get('id');
        
        if ($id) {
            // Edit mode - fetch supplier data from database
            $item = Supplier::findOrFail($id);
            $title = __('general.edit') . ' ' . __('locale.suppliers');
        } else {
            // Create mode
            $item = null;
            $title = __('general.add_new') . ' ' . __('locale.suppliers');
        }
        
        $requestUrl = route('center_user.suppliers.updateOrCreate');
        
        return view('CenterUser.SubViews.Suppliers.index', compact('title', 'requestUrl', 'item'));
    }

    public function updateOrCreate(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:suppliers,email,' . ($request->id ?? 'NULL'),
            'phone' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        foreach (config('translatable.locales') as $locale) {
            $rules["$locale.description"] = 'nullable|string|max:1000';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->only(['name', 'email', 'phone']);
            
            foreach (config('translatable.locales') as $locale) {
                $data[$locale] = [
                    'description' => $request->input("$locale.description")
                ];
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                if ($request->id && $request->old_logo) {
                    Storage::disk('public')->delete($request->old_logo);
                }
                $data['logo'] = $request->file('logo')->store('suppliers/logos', 'public');
            } elseif ($request->id) {
                // Keep existing logo if no new one uploaded
                $existingSupplier = Supplier::find($request->id);
                $data['logo'] = $existingSupplier->logo;
            }

            if ($request->id) {
                // Update existing supplier
                $supplier = Supplier::findOrFail($request->id);
                $supplier->update($data);
                $message = __('general.updated_successfully');
            } else {
                // Create new supplier
                $supplier = Supplier::create($data);
                $message = __('general.created_successfully');
            }

            // Redirect to suppliers list with success message
            return redirect()->route('center_user.suppliers.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', __('general.error_occurred') . ': ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateSupplier(Request $request)
    {
        return $this->updateOrCreate($request);
    }
}
