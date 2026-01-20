<?php

namespace App\Helpers;

class MyHelper
{
    public static function responseJSON($message, $code, $data = [], $paginationData = [])
    {
        if ($paginationData) {
            return response()->json([
                'message' => $message,
                'data' => $data,
                'paginationData' => $paginationData
            ], $code);
        }
        return response()->json([
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function uploads($name): string
    {
        return url('/storage/uploads/') . '/' . $name;
    }

    public static function calcDistance($latFrom, $lngFrom, $arrayTos)
    {
        $earthRadius = 6371000;

        $latFrom = deg2rad($latFrom);
        $lngFrom = deg2rad($lngFrom);

        $min = 999999999999;
        $id = 0;

        foreach ($arrayTos as $to) {
            $latTo = deg2rad($to['lat']);
            $lngTo = deg2rad($to['lng']);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lngTo - $lngFrom;

            $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $temp = $angle * $earthRadius;

            if ($min > $temp) {
                $min = $temp;
                $id = $to['id'];
            }
        }

        return $id;
    }

    public static function getDistanceFromLatLonInKm($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // Radius of the earth in km
        $dLat = deg2rad($lat2 - $lat1);  // deg2rad below
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $R * $c; // Distance in km
        return $d;
    }

    public static function generateCode($length)
    {
        return bin2hex(random_bytes($length / 2));
    }

    public static function truncateWithReadMore($text, $length = 50)
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $shortText = mb_substr($text, 0, $length);
        $readMore = __('general.read_more');

        return '<span class="short-text">' . htmlspecialchars($shortText) . '...</span>' .
               '<span class="full-text" style="display:none;">' . htmlspecialchars($text) . '</span>' .
               ' <a href="javascript:void(0);" class="read-more-btn text-primary nowrap" style="font-size: 0.8rem;">' . $readMore . '</a>';
    }
}
