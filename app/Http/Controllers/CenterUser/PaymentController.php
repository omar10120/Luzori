<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Models\Center;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Subscription plans: amount => renewal period in days.
     */
    public static function subscriptionPlans(): array
    {
        return [
            '1_month' => [
                'symbol' => 'AED',
                'amount' => 149,
                'days'   => 30,
                'label'  => '1 Month',
            ],
            '2_months' => [
                'symbol' => 'AED',
                'amount' => 200,
                'days'   => 60,
                'label'  => '2 Months',
            ],
            '1_year' => [
                'symbol' => 'AED',
                'amount' => 1499,
                'days'   => 365,
                'label'  => '1 Year',
            ],
        ];
    }

    public function plans()
    {
        $center    = $this->resolveActiveCenter();
        $isExpired = $center && $center->expire_date && now()->gt($center->expire_date);
        $expireAt  = $center?->expire_date ? Carbon::parse($center->expire_date) : null;

        return view('CenterUser.SubViews.Subscription.plans', [
            'title'                  => __('field.payment'),
            'plans'                  => self::subscriptionPlans(),
            'isExpired'              => $isExpired,
            'hasSubscription'        => (bool) $expireAt,
            'expireDate'             => $expireAt,
            'expireDateFormatted'    => $expireAt?->format('Y-m-d H:i'),
            'myfatoorahSessionJsUrl' => env(
                'MYFATOORAH_SESSION_JS_URL',
                'https://demo.myfatoorah.com/sessions/v1/session.js'
            ),
            'createSessionUrl' => route('center_user.subscription.create-session'),
            'callbackUrl'      => route('center_user.subscription.callback'),
        ]);
    }

    private function resolveActiveCenter(): ?Center
    {
        $host = request()->getHost();

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return Center::where('domain', 'center8')->first();
        }

        $domain = session('active_center_domain');

        if ($domain) {
            return Center::where('domain', $domain)->first();
        }

        $parts     = explode('.', $host);
        $subdomain = count($parts) > 2 && $parts[0] !== 'www' ? $parts[0] : null;

        return $subdomain ? Center::where('domain', $subdomain)->first() : null;
    }

    /**
     * Create a MyFatoorah payment session for subscription renewal.
     */
    public function createSession(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:' . implode(',', array_keys(self::subscriptionPlans())),
        ]);

        $plan   = self::subscriptionPlans()[$request->plan];
        $amount = (float) $plan['amount'];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('MYFATOORAH_TOKEN'),
                'Content-Type'  => 'application/json',
            ])->post(rtrim(env('MYFATOORAH_BASE_URL'), '/') . '/v3/sessions', [
                'PaymentMode' => 'COMPLETE_PAYMENT',
                'Order'       => [
                    'Amount' => $amount,
                ],
            ]);
            Log::info(' ', ['response' => $response->json()]);
            if ($response->successful() && ($response->json('IsSuccess') === true)) {
                $data = $response->json('Data');

                session([
                    'pending_subscription_plan' => [
                        'key'    => $request->plan,
                        'amount' => $amount,
                        'days'   => $plan['days'],
                    ],
                    'myfatoorah_encryption_key' => $data['EncryptionKey'],
                ]);
                Log::info(' ', ['data' => $data]);
                return response()->json([
                    'success'        => true,
                    'session_id'     => $data['SessionId'],
                    'encryption_key' => $data['EncryptionKey'],
                    'amount'         => $amount,
                    'symbol'         => $plan['symbol'],
                    'days'           => $plan['days'],
                    'label'          => $plan['label'],
                ]);
            }

            Log::error('MyFatoorah createSession failed', ['response' => $response->json()]);

            return response()->json([
                'success' => false,
                'message' => $response->json('Message') ?? __('api.unknownError'),
            ], 500);
        } catch (\Exception $e) {
            Log::error('MyFatoorah createSession exception: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => __('api.unknownError')], 500);
        }
    }

    /**
     * Handle the embedded payment callback and extend the center's subscription.
     */
    public function callback(Request $request)
    {
        $paymentData      = $request->input('paymentData');
        $encryptionKey    = $request->input('encryptionKey') ?: session('myfatoorah_encryption_key');
        $paymentCompleted = (bool) $request->input('paymentCompleted', false);

        Log::info('paymentCompleted', ['paymentCompleted' => $paymentCompleted]);
        Log::info('paymentData', ['paymentData' => $paymentData]);
        Log::info('encryptionKey', ['encryptionKey' => $encryptionKey]);
        if (!$paymentCompleted || !$paymentData || !$encryptionKey) {
            return response()->json(['success' => false, 'message' => 'Payment not completed or missing data.'], 400);
        }

        try {
            
            $decrypted = $this->decryptPaymentData($paymentData, $encryptionKey);
            $paymentResult = json_decode($decrypted, true);
            
            Log::info('paymentResult', ['paymentResult' => $paymentResult]);
            // $paymentSucceeded = !empty($paymentResult['"Transaction.']) || !empty($paymentResult['isSuccess']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('Invalid payment JSON', [
                    'raw' => $decrypted
                ]);
            
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment data.'
                ], 400);
            }
            $invoiceStatus = data_get($paymentResult, 'Invoice.Status');
            $transactionStatus = data_get($paymentResult, 'Transaction.Status');

            $paymentSucceeded =
                $invoiceStatus === 'PAID' &&
                $transactionStatus === 'SUCCESS';

            if (!$paymentSucceeded) {
                Log::warning('Payment not successful', [
                    'invoice_status' => $invoiceStatus,
                    'transaction_status' => $transactionStatus,
                    'paymentResult' => $paymentResult,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed.'
                ], 400);
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

            $pendingPlan = session('pending_subscription_plan', []);
            $days        = (int) ($pendingPlan['days'] ?? 30);

            $currentExpire = $centerRow->expire_date ? Carbon::parse($centerRow->expire_date) : null;
            $base          = ($currentExpire && now()->lt($currentExpire)) ? $currentExpire : now();
            $newExpire     = (clone $base)->addDays($days);

            DB::connection('central')
                ->table('centers')
                ->where('domain', $centerDomain)
                ->update(['expire_date' => $newExpire]);

            session()->forget(['pending_subscription_plan', 'myfatoorah_encryption_key']);

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
