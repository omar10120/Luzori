<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Create a MyFatoorah payment session for subscription renewal.
     */
    public function createSession(Request $request)
    {
        $amount   = (float) env('SUBSCRIPTION_AMOUNT', 10);
        $currency = env('SUBSCRIPTION_CURRENCY', 'AED');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MYFATOORAH_TOKEN'),
                'Content-Type'  => 'application/json',
            ])->post(rtrim(env('MYFATOORAH_BASE_URL'), '/') . '/v3/sessions', [
                'PaymentMode' => 'COMPLETE_PAYMENT',
                'Order'       => [
                    'Amount'   => $amount,
                    'Currency' => $currency,
                ],
            ]);

            if ($response->successful() && ($response->json('IsSuccess') === true)) {
                $data = $response->json('Data');
                return response()->json([
                    'success'        => true,
                    'session_id'     => $data['SessionId'],
                    'encryption_key' => $data['EncryptionKey'],
                    'amount'         => $amount,
                    'currency'       => $currency,
                ]);
            }

            Log::error('MyFatoorah createSession failed', ['response' => $response->json()]);
            return response()->json(['success' => false, 'message' => __('api.unknownError')], 500);
        } catch (\Exception $e) {
            Log::error('MyFatoorah createSession exception: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => __('api.unknownError')], 500);
        }
    }

    /**
     * Handle the embedded payment callback and extend the center's subscription.
     * Decrypts paymentData using AES-128-CBC (key = IV = first 16 bytes of EncryptionKey).
     */
    public function callback(Request $request)
    {
        $paymentData      = $request->input('paymentData');
        $encryptionKey    = $request->input('encryptionKey');
        $paymentCompleted = (bool) $request->input('paymentCompleted', false);

        if (!$paymentCompleted || !$paymentData || !$encryptionKey) {
            return response()->json(['success' => false, 'message' => 'Payment not completed or missing data.'], 400);
        }

        try {
            $decrypted     = $this->decryptPaymentData($paymentData, $encryptionKey);
            $paymentResult = json_decode($decrypted, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($paymentResult['IsSuccess'])) {
                Log::warning('Payment verification failed — decrypted payload', ['raw' => $decrypted]);
                return response()->json(['success' => false, 'message' => 'Payment verification failed.'], 400);
            }

            $centerDomain = session('active_center_domain');
            if (!$centerDomain) {
                return response()->json(['success' => false, 'message' => 'Center not identified.'], 400);
            }

            $centerRow = DB::connection('central')
                ->table('centers')
                ->where('domain', $centerDomain)
                ->select('id', 'expire_date')
                ->first();

            if (!$centerRow) {
                return response()->json(['success' => false, 'message' => 'Center not found.'], 404);
            }

            $days          = (int) env('SUBSCRIPTION_PLAN_DAYS', 30);
            $currentExpire = $centerRow->expire_date ? Carbon::parse($centerRow->expire_date) : null;
            // Extend from current expiry if still active, otherwise extend from now
            $base      = ($currentExpire && now()->lt($currentExpire)) ? $currentExpire : now();
            $newExpire = (clone $base)->addDays($days);

            DB::connection('central')
                ->table('centers')
                ->where('domain', $centerDomain)
                ->update(['expire_date' => $newExpire]);

            return response()->json([
                'success'    => true,
                'message'    => 'Subscription renewed successfully.',
                'new_expiry' => $newExpire->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    /**
     * Decrypt MyFatoorah paymentData.
     * Algorithm: AES-128-CBC, key = IV = first 16 bytes of the EncryptionKey string.
     */
    private function decryptPaymentData(string $encryptedText, string $encryptionKey): string
    {
        $key  = str_pad(substr($encryptionKey, 0, 16), 16, "\0");
        $iv   = $key;
        $data = openssl_decrypt(
            base64_decode($encryptedText),
            'AES-128-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $data !== false ? $data : 'FAILED';
    }
}
