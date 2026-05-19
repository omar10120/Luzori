<?php

namespace App\Http\Controllers\CenterAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function createSession(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('MYFATOORAH_TOKEN'),
            'Content-Type' => 'application/json',
        ])->post(env('MYFATOORAH_BASE_URL') . '/v3/sessions', [
            'PaymentMode' => 'COMPLETE_PAYMENT',
            'Order' => [
                'Amount' => $request->amount,   
                'Currency' => $request->currency ?? 'AED',
            ],
        ]);

        if ($response->successful()) {
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $response->json());
        }

        return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR, $response->json());
    }
}
