<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'types'
    ];

    protected $casts = [
        'types' => 'array',
    ];

    // Payment method types
    const TYPE_BOOKING = 'booking';
    const TYPE_PRODUCT = 'product';
    const TYPE_WALLET = 'wallet';
    const TYPE_TIPS = 'tips';
    const TYPE_GENERAL = 'general';

    public static function getTypes()
    {
        return [
            self::TYPE_BOOKING => 'Booking',
            self::TYPE_PRODUCT => 'Product',
            self::TYPE_WALLET => 'Wallet',
            self::TYPE_TIPS => 'Tips',
            self::TYPE_GENERAL => 'General'
        ];
    }

    // Scope methods for filtering by type
    public function scopeForBooking($query)
    {
        return $query->whereJsonContains('types', self::TYPE_BOOKING)->orWhereJsonContains('types', self::TYPE_GENERAL);
    }

    public function scopeForProduct($query)
    {
        return $query->whereJsonContains('types', self::TYPE_PRODUCT)->orWhereJsonContains('types', self::TYPE_GENERAL);
    }

    public function scopeForWallet($query)
    {
        return $query->whereJsonContains('types', self::TYPE_WALLET)->orWhereJsonContains('types', self::TYPE_GENERAL);
    }

    public function scopeForGeneral($query)
    {
        return $query->whereJsonContains('types', self::TYPE_GENERAL);
    }

    public function scopeForTips($query)
    {
        return $query->whereJsonContains('types', self::TYPE_TIPS)->orWhereJsonContains('types', self::TYPE_GENERAL);
    }

    // Helper method to check if payment method supports a specific type
    public function supportsType($type)
    {
        return in_array($type, $this->types ?? []) || in_array(self::TYPE_GENERAL, $this->types ?? []);
    }
}
