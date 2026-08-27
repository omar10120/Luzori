<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\Booking;
use App\Models\User as TenantUser;
use Illuminate\Support\Facades\Log;

class BookingNotificationService
{
    public function __construct(protected AppNotificationService $appNotificationService)
    {
    }

    public function notifyStatusChange(Booking $booking, string $status): void
    {
        try {
            $appUser = $this->resolveAppUser($booking);
            if (!$appUser) {
                return;
            }

            if ($status === 'confirmed') {
                $translations = [
                    'ar' => [
                        'title' => 'تم تأكيد الحجز',
                        'text' => 'تم تأكيد حجزك بنجاح.',
                    ],
                    'en' => [
                        'title' => 'Booking Confirmed',
                        'text' => 'Your booking has been confirmed successfully.',
                    ],
                ];
                $type = 'booking_confirmed';
            } elseif ($status === 'rejected') {
                $translations = [
                    'ar' => [
                        'title' => 'تم رفض الحجز',
                        'text' => 'تم رفض حجزك. تم استرجاع المبلغ إلى محفظتك إن وُجد.',
                    ],
                    'en' => [
                        'title' => 'Booking Rejected',
                        'text' => 'Your booking was rejected. Any paid amount has been refunded to your wallet if applicable.',
                    ],
                ];
                $type = 'booking_rejected';
            } else {
                return;
            }

            $this->appNotificationService->notifyAppUser($appUser, $translations, $type, [
                'booking_id' => (string) $booking->id,
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('Booking status notification failed: ' . $e->getMessage());
        }
    }

    protected function resolveAppUser(Booking $booking): ?AppUser
    {
        $email = null;
        $phone = null;

        if ($booking->user) {
            $email = $booking->user->email;
            $phone = $booking->user->phone;
        }

        if (!$phone && $booking->mobile) {
            $phone = $booking->mobile;
        }

        if (!$email && !$phone) {
            return null;
        }

        return AppUser::query()
            ->where(function ($q) use ($email, $phone) {
                if ($email) {
                    $q->where('email', $email);
                }
                if ($phone) {
                    $email ? $q->orWhere('phone', $phone) : $q->where('phone', $phone);
                }
            })
            ->first();
    }
}
