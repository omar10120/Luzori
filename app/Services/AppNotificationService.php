<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\AppUser;
use App\Models\Center;
use App\Models\CenterUser;
use App\Models\FirebaseSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as MessagingNotification;

class AppNotificationService
{
    public function sendAdminNotification(array $data): ?AppNotification
    {
        $targetType = $data['target_type'] ?? 'users';
        $recipients = $data['recipients'] ?? ['all'];
        $sendToAll = in_array('all', $recipients, true);

        if ($targetType === 'users') {
            $ids = $sendToAll
                ? AppUser::query()->where('is_active', 1)->pluck('id')->all()
                : array_map('intval', array_filter($recipients, fn ($id) => $id !== 'all'));
        } else {
            $ids = $sendToAll
                ? Center::query()->where('status', 'approve')->pluck('id')->all()
                : array_map('intval', array_filter($recipients, fn ($id) => $id !== 'all'));
        }

        if (empty($ids)) {
            return null;
        }

        $imagePath = null;
        if (!empty($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $imagePath = $data['image']->store('notifications', 'public');
        }

        $locales = Config::get('translatable.locales', ['ar', 'en']);
        $translations = [];
        foreach ($locales as $locale) {
            if (!empty($data[$locale]['title'])) {
                $translations[$locale] = [
                    'title' => $data[$locale]['title'],
                    'text' => $data[$locale]['text'] ?? '',
                ];
            }
        }

        // Fallback: single title/description fields
        if (empty($translations) && !empty($data['title'])) {
            foreach ($locales as $locale) {
                $translations[$locale] = [
                    'title' => $data['title'],
                    'text' => $data['description'] ?? $data['text'] ?? '',
                ];
            }
        }

        if (empty($translations)) {
            return null;
        }

        DB::connection('central')->beginTransaction();
        try {
            $notification = AppNotification::create(array_merge($translations, [
                'image' => $imagePath,
                'target_type' => $targetType,
                'status' => true,
                'sent_count' => count($ids),
                'type' => 'admin',
            ]));

            if ($targetType === 'users') {
                foreach ($ids as $id) {
                    $user = AppUser::find($id);
                    if ($user) {
                        $user->appNotifications()->attach($notification->id, ['is_read' => 0]);
                    }
                }
                $this->pushToAppUsers($ids, $translations, $notification);
            } else {
                foreach ($ids as $id) {
                    $center = Center::find($id);
                    if ($center) {
                        $center->notifications()->attach($notification->id, ['is_read' => 0]);
                    }
                }
                $this->pushToCenters($ids, $translations, $notification);
            }

            DB::connection('central')->commit();
            return $notification;
        } catch (\Throwable $e) {
            DB::connection('central')->rollBack();
            Log::error('Admin notification failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    public function resend(int $notificationId): bool
    {
        $notification = AppNotification::with(['translations', 'appUsers', 'centers'])->find($notificationId);
        if (!$notification || !$notification->status) {
            return false;
        }

        $translations = [];
        foreach ($notification->translations as $t) {
            $translations[$t->locale] = ['title' => $t->title, 'text' => $t->text];
        }

        if ($notification->target_type === 'users') {
            $ids = $notification->appUsers->pluck('id')->all();
            $this->pushToAppUsers($ids, $translations, $notification);
        } else {
            $ids = $notification->centers->pluck('id')->all();
            $this->pushToCenters($ids, $translations, $notification);
        }

        $notification->increment('sent_count', max(1, count($ids)));
        return true;
    }

    public function toggleStatus(int $notificationId): ?AppNotification
    {
        $notification = AppNotification::find($notificationId);
        if (!$notification) {
            return null;
        }
        $notification->update(['status' => !$notification->status]);
        return $notification->fresh();
    }

    /**
     * Instant notification for a single AppUser (e.g. booking status).
     */
    public function notifyAppUser(AppUser $user, array $translations, string $type = 'system', array $extra = []): ?AppNotification
    {
        DB::connection('central')->beginTransaction();
        try {
            $notification = AppNotification::create(array_merge($translations, [
                'target_type' => 'users',
                'status' => true,
                'sent_count' => 1,
                'type' => $type,
            ]));

            $user->appNotifications()->attach($notification->id, ['is_read' => 0]);
            $this->pushToAppUsers([$user->id], $translations, $notification, $extra);

            DB::connection('central')->commit();
            return $notification;
        } catch (\Throwable $e) {
            DB::connection('central')->rollBack();
            Log::error('AppUser notification failed: ' . $e->getMessage());
            return null;
        }
    }

    public function getForAppUser(AppUser $user, int $perPage = 20)
    {
        return $user->appNotifications()
            ->with('translations')
            ->orderByDesc('notifications.id')
            ->paginate($perPage);
    }

    public function markSeen(AppUser $user, ?int $notificationId = null): bool
    {
        if ($notificationId) {
            $user->appNotifications()->updateExistingPivot($notificationId, ['is_read' => 1]);
            return true;
        }

        $ids = $user->appNotifications()->wherePivot('is_read', 0)->pluck('notifications.id');
        foreach ($ids as $id) {
            $user->appNotifications()->updateExistingPivot($id, ['is_read' => 1]);
        }
        return true;
    }

    public function getForCenter(Center $center, int $perPage = 20)
    {
        return $center->notifications()
            ->with('translations')
            ->orderByDesc('notifications.id')
            ->paginate($perPage);
    }

    public function navbarForCenter(Center $center, int $limit = 10): array
    {
        $items = $center->notifications()
            ->with('translations')
            ->orderByDesc('notifications.id')
            ->limit($limit)
            ->get();

        $unread = $center->notifications()->wherePivot('is_read', 0)->count();

        return [
            'items' => $items,
            'unread' => $unread,
        ];
    }

    public function navbarForAdmin(int $limit = 10): array
    {
        $items = AppNotification::query()
            ->with('translations')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return [
            'items' => $items,
            'unread' => 0,
        ];
    }

    public function markSeenForCenter(Center $center, ?int $notificationId = null): bool
    {
        if ($notificationId) {
            $center->notifications()->updateExistingPivot($notificationId, ['is_read' => 1]);
            return true;
        }

        $ids = $center->notifications()->wherePivot('is_read', 0)->pluck('notifications.id');
        foreach ($ids as $id) {
            $center->notifications()->updateExistingPivot($id, ['is_read' => 1]);
        }
        return true;
    }

    protected function pushToAppUsers(array $userIds, array $translations, AppNotification $notification, array $extra = []): void
    {
        $title = $translations['ar']['title'] ?? ($translations['en']['title'] ?? '');
        $body = $translations['ar']['text'] ?? ($translations['en']['text'] ?? '');

        $tokens = AppUser::whereIn('id', $userIds)
            ->with('fcmTokens')
            ->get()
            ->flatMap(fn ($u) => $u->fcmTokens->pluck('token'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->sendMulticast($title, $body, $tokens, array_merge([
            'action' => $notification->type ?? 'info',
            'notification_id' => (string) $notification->id,
            'image' => $notification->image_url ?? '',
        ], $extra));
    }

    protected function pushToCenters(array $centerIds, array $translations, AppNotification $notification): void
    {
        $title = $translations['ar']['title'] ?? ($translations['en']['title'] ?? '');
        $body = $translations['ar']['text'] ?? ($translations['en']['text'] ?? '');

        $allTokens = [];
        $previousDb = config('database.connections.mysql.database');

        foreach (Center::whereIn('id', $centerIds)->get() as $center) {
            // Center-level tokens on central DB
            $allTokens = array_merge($allTokens, $center->fcmTokens()->pluck('token')->all());

            if (empty($center->database)) {
                continue;
            }

            try {
                Config::set('database.connections.mysql.database', $center->database);
                DB::purge('mysql');
                DB::reconnect('mysql');

                $staffTokens = CenterUser::query()
                    ->with('fcmTokens')
                    ->get()
                    ->flatMap(fn ($cu) => $cu->fcmTokens->pluck('token'))
                    ->filter()
                    ->all();

                $allTokens = array_merge($allTokens, $staffTokens);
            } catch (\Throwable $e) {
                Log::warning("FCM tokens for center {$center->id}: " . $e->getMessage());
            }
        }

        Config::set('database.connections.mysql.database', $previousDb);
        DB::purge('mysql');
        DB::reconnect('mysql');

        $allTokens = array_values(array_unique(array_filter($allTokens)));

        $this->sendMulticast($title, $body, $allTokens, [
            'action' => $notification->type ?? 'info',
            'notification_id' => (string) $notification->id,
            'image' => $notification->image_url ?? '',
        ]);
    }

    public function sendMulticast(string $title, string $body, array $tokens, array $data = []): bool
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return false;
        }

        try {
            $messaging = $this->messaging();
            $message = CloudMessage::new()
                ->withNotification(MessagingNotification::fromArray([
                    'title' => $title,
                    'body' => $body,
                ]))
                ->withData(array_map('strval', $data));

            $valid = $messaging->validateRegistrationTokens($tokens)['valid'] ?? [];
            if (empty($valid)) {
                return false;
            }

            $messaging->sendMulticast($message, $valid);
            return true;
        } catch (\Throwable $e) {
            Log::error('FCM sendMulticast failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function messaging()
    {
        $setting = FirebaseSetting::current();
        $serviceAccount = $setting?->serviceAccountArray();

        if ($serviceAccount) {
            return (new Factory)->withServiceAccount($serviceAccount)->createMessaging();
        }

        $file = config('firebase.credentials.file');
        if ($file && is_file($file)) {
            return (new Factory)->withServiceAccount($file)->createMessaging();
        }

        return \Kreait\Laravel\Firebase\Facades\Firebase::messaging();
    }
}
