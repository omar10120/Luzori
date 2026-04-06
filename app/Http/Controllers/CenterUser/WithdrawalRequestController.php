<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\WithdrawalRequestDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\CenterWithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WithdrawalRequestController extends Controller
{
    private $plural = 'withdrawal_requests';

    public function index(WithdrawalRequestDataTable $dataTable)
    {
        // Get center from connection
        $currentDb = config('database.connections.mysql.database');
        $center = Center::where('database', $currentDb)->first();

        $title = __('api.withdrawal_requests') ?? 'Withdrawal Requests';
        $walletBalance = $center ? $center->wallet : 0;

        // Ensure we pass a subview that renders the top header correctly
        return $dataTable->render('CenterUser.SubViews.WithdrawalRequest.index', compact('title', 'walletBalance'));
    }

    public function store(Request $request)
    {
        // Get center from connection
        $currentDb = config('database.connections.mysql.database');
        $center = Center::where('database', $currentDb)->first();

        if (!$center) {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($center->wallet <= 0) {
            return MyHelper::responseJSON(__('api.insufficient_balance') ?? 'Insufficient Balance', Response::HTTP_BAD_REQUEST);
        }

        // Deduct remaining wallet and create request
        $amount = $center->wallet;
        
        $withdrawalRequest = CenterWithdrawalRequest::create([
            'center_id' => $center->id,
            'amount' => $amount,
            'status' => 'pending'
        ]);

        if ($withdrawalRequest) {
            // Set wallet back to 0
            $center->update(['wallet' => 0]);
            return MyHelper::responseJSON(__('api.doneSuccessfully') ?? 'Done Successfully', Response::HTTP_CREATED);
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
