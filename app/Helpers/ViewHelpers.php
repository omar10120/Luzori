<?php

function get_user_role()
{
    if (auth('center_api')->check()) {
        return auth('center_api')->user()->roles->pluck("id")->first();
    }
    if (str_contains(url()->current(), 'center_user')) {
        return auth('center_user')->user()->roles->pluck("id")->first();
    } else {
        return null;
    }
}

function get_num_format($number, $decimals = 2)
{
    if ($number === null || $number === '') {
        return number_format(0, $decimals);
    }
    return number_format($number, $decimals);
}

function get_currency()
{
    try {
            $centerUser = auth('center_user')->user();
            if (isset($centerUser->currency) && !empty($centerUser->currency)) {
                return $centerUser->currency . " ";
            }
        return "AED ";
    } catch (\Exception $e) {
       
        return "AED ";
    }
}

function get_symbol_type($type = "")
{
    if ($type == "member_ship") {
        return "( F )";
    }

    if ($type == "discount_code") {
        return "( DS )";
    }

    if ($type == "free_service") {
        return "©";
    }
    return "";
}

function get_color_type($type = "")
{
    if ($type == "member_ship") {
        return "#8B3A3A";
    }

    if ($type == "discount_code") {
        return "#C62828"; // clear red for discounts
    }

    if ($type == "free_service") {
        return "#6B4A0F";
    }
    return "#1A3A36";
}

function my_date($date, $with_time = false)
{
    if (empty($date)) {
        return "";
    }
    if (!is_numeric($date)) {
        $date = strtotime($date);
    }
    return ($with_time) ? date('d/m/Y h:i:s A', $date) : date('d/m/Y', $date);
}



function get_payment_method_names($paymentType = null)
{
    $query = \App\Models\PaymentMethod::query();
    
    if ($paymentType) {
        $query->whereJsonContains('types', $paymentType)->orWhereJsonContains('types', 'general');
    }
    
    return $query->pluck('name')->toArray();
}

function get_payment_types($type = "", $paymentType = null)
{
    $query = \App\Models\PaymentMethod::query();
    
    if ($paymentType) {
        $query->whereJsonContains('types', $paymentType)->orWhereJsonContains('types', 'general');
    }
    
    $paymentMethods = $query->pluck('name', 'name')->toArray();
    
    if (!empty($type)) {
        return (isset($paymentMethods[$type])) ? $paymentMethods[$type] : '';
    }
    return $paymentMethods;
}

function get_payment_methods_by_type($paymentType)
{
    return \App\Models\PaymentMethod::whereJsonContains('types', $paymentType)
        ->orWhereJsonContains('types', 'general')
        ->get();
}

function has_commission_permission()
{
    try {
        if (auth('center_user')->check()) {
            return auth('center_user')->user()->can('VIEW_COMMISSION', 'center_api');
        }
        if (auth('center_api')->check()) {
            return auth('center_api')->user()->can('VIEW_COMMISSION', 'center_api');
        }
        if (auth('center')->check()) {
            return auth('center')->user()->can('VIEW_COMMISSION', 'center');
        }
        return false;
    } catch (\Exception $e) {
        return false;
    }
}


function get_allowed_commission_type($type = 'booking')
{
    try {
        $user = null;
        $guard = null;
        
        if (auth('center_user')->check()) {
            $user = auth('center_user')->user();
            $guard = 'center_api';
        } elseif (auth('center_api')->check()) {
            $user = auth('center_api')->user();
            $guard = 'center_api';
        } elseif (auth('center')->check()) {
            $user = auth('center')->user();
            $guard = 'center';
        }
        
        if (!$user) {
            return null;
        }
        
        // Check if user has VIEW_COMMISSION permission
        if (!$user->can('VIEW_COMMISSION', $guard)) {
            return null;
        }
        
        // Check which commission type permission the user has
        $typeUpper = strtoupper($type);
        if ($user->can('COMMISSION_' . $typeUpper . '_PERCENTAGE', $guard)) {
            return 'percentage';
        } elseif ($user->can('COMMISSION_' . $typeUpper . '_FIXED', $guard)) {
            return 'fixed';
        }
        
        return null;
    } catch (\Exception $e) {
        return null;
    }
}

function get_allowed_commission_rates(?string $type = null): array
{
    $fallback = [10, 6.6];

    try {
        $raw = \App\Models\Setting::query()->where('key', 'commission_percentage_rates')->value('value');
        if ($raw === null || $raw === '') {
            return $fallback;
        }

        $stored = json_decode((string) $raw, true);
        if (!is_array($stored)) {
            return $fallback;
        }

        $values = $stored;
        if ($type !== null) {
            $values = $stored[strtolower($type)] ?? null;
            if (!is_array($values)) {
                $values = array_is_list($stored) ? $stored : $fallback;
            }
        }

        $rates = [];
        foreach ($values as $value) {
            $rate = round((float) $value, 2);
            if ($rate > 0 && $rate <= 100) {
                $rates[] = $rate;
            }
        }

        $rates = array_values(array_unique($rates, SORT_REGULAR));

        return $rates;
    } catch (\Throwable $e) {
        return $fallback;
    }
}

function resolve_stored_commission($commission, ?string $type, float $base, bool $legacyNullIsPercentage = true): float
{
    if ($commission === null || $commission === '') {
        return 0;
    }

    $value = floatval($commission);
    if ($type === 'fixed') {
        return $value;
    }
    if ($type === 'percentage' || ($type === null && $legacyNullIsPercentage)) {
        return ($base * $value) / 100;
    }

    return $value;
}
