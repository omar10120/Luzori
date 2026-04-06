<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\WithdrawalRequestDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\CenterWithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WithdrawalRequestController extends Controller
{
    private $plural = 'withdrawal_requests';

    public function index(WithdrawalRequestDataTable $dataTable)
    {
        $title = __('api.withdrawal_requests') ?? 'Withdrawal Requests';
        return $dataTable->render('Admin.SubViews.WithdrawalRequest.index', compact('title'));
    }

    public function approve($id)
    {
        $request = CenterWithdrawalRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_BAD_REQUEST);
        }

        $request->update(['status' => 'confirmed']);

        return MyHelper::responseJSON(__('api.editSuccessfully') ?? 'Approved successfully', Response::HTTP_OK);
    }

    public function reject(Request $requestData, $id)
    {
        $requestData->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        $request = CenterWithdrawalRequest::with('center')->findOrFail($id);

        if ($request->status !== 'pending') {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_BAD_REQUEST);
        }

        // Refund wallet
        if ($request->center) {
            $request->center->incrementWallet($request->amount);
        }

        $request->update([
            'status' => 'rejected',
            'admin_notes' => $requestData->admin_notes
        ]);

        return MyHelper::responseJSON(__('api.editSuccessfully') ?? 'Rejected successfully', Response::HTTP_OK);
    }
}
