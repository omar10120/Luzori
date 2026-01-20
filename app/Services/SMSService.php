<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SMSService
{
    public function sendSMS(User $user)
    {
        DB::beginTransaction();
        $code = strval(mt_rand(1000, 9999));
        $message =  $this->generateMessage($code);
        $mobile = $this->preprocessMobile($user->mobile);

        date_default_timezone_set('Asia/Damascus');

        //Api Send SMS
        $url = 'https://services.mtnsyr.com:7443/general/MTNSERVICES/ConcatenatedSender.aspx?User=klsh545&Pass=lesa151415&From=Klshi%20Wasel&&Gsm=' . urlencode($mobile) . '&Msg=' . urlencode($message) . '&Lang=1';
        $context = stream_context_create(array(
            'http' => array('ignore_errors' => true, 'method' => 'POST')
        ));
        $response = file_get_contents($url, false, $context);
        $response = explode("-", $response);
        $user->update([
            'verification_code' => $code,
        ]);
        DB::commit();
        return $response;
    }

    public function generateMessage(string $code): string
    {
        $message =  nl2br("W-" . $code . " is your verification code. ");
        return $message;
    }

    public function preprocessMobile(string $mobile): string
    {
        $mobile = ltrim($mobile, '+963');
        $mobile = ltrim($mobile, '963');
        $mobile = ltrim($mobile, '0');
        $mobile = "963" . $mobile;
        return $mobile;
    }
}
