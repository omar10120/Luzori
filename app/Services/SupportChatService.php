<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Center;
use App\Models\CenterUser;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupportChatService
{
    public function __construct(
        protected FirebaseRealtimeService $firebase,
        protected AppNotificationService $notifications,
    ) {}

    public function ensureConversation(Center $center): SupportConversation
    {
        return SupportConversation::firstOrCreate(
            ['center_id' => $center->id],
            [
                'last_message' => null,
                'last_message_at' => null,
                'unread_admin' => 0,
                'unread_center' => 0,
            ]
        );
    }

    public function listConversationsForAdmin()
    {
        $existing = SupportConversation::query()
            ->with('center:id,name,domain,status')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get()
            ->keyBy('center_id');

        // Include centers that never chatted yet
        $centers = Center::query()
            ->where('status', 'approve')
            ->orderBy('name')
            ->get(['id', 'name', 'domain', 'status']);

        return $centers->map(function (Center $center) use ($existing) {
            $conv = $existing->get($center->id);
            return [
                'center_id' => $center->id,
                'center_name' => $center->name,
                'center_domain' => $center->domain,
                'conversation_id' => $conv?->id,
                'last_message' => $conv?->last_message,
                'last_sender_name' => $conv?->last_sender_name,
                'last_message_at' => optional($conv?->last_message_at)->toIso8601String(),
                'last_message_human' => optional($conv?->last_message_at)->diffForHumans(),
                'unread' => (int) ($conv?->unread_admin ?? 0),
            ];
        })->sortByDesc(function ($row) {
            return $row['last_message_at'] ?: '1970-01-01';
        })->values();
    }

    public function messages(SupportConversation $conversation, int $afterId = 0, int $limit = 100)
    {
        $q = $conversation->messages()->orderBy('id');
        if ($afterId > 0) {
            $q->where('id', '>', $afterId);
        } else {
            // latest page
            $ids = $conversation->messages()->orderByDesc('id')->limit($limit)->pluck('id');
            return SupportMessage::whereIn('id', $ids)->orderBy('id')->get()->map->toChatArray()->values();
        }

        return $q->limit($limit)->get()->map->toChatArray()->values();
    }

    public function sendFromAdmin(Admin $admin, Center $center, string $body): SupportMessage
    {
        $conversation = $this->ensureConversation($center);
        $role = method_exists($admin, 'getRoleNames')
            ? ($admin->getRoleNames()->first() ?: 'Admin')
            : 'Admin';

        return $this->storeMessage(
            $conversation,
            $center,
            'admin',
            (int) $admin->id,
            $admin->name,
            $role,
            $body,
            notifyCenter: true,
            notifyAdmin: false,
        );
    }

    public function sendFromCenterUser(CenterUser $user, Center $center, string $body): SupportMessage
    {
        $conversation = $this->ensureConversation($center);
        $role = method_exists($user, 'getRoleNames')
            ? ($user->getRoleNames()->first() ?: 'Staff')
            : 'Staff';

        return $this->storeMessage(
            $conversation,
            $center,
            'center_user',
            (int) $user->id,
            $user->name,
            $role,
            $body,
            notifyCenter: false,
            notifyAdmin: true,
        );
    }

    public function markReadForAdmin(SupportConversation $conversation): void
    {
        $conversation->update(['unread_admin' => 0]);
    }

    public function markReadForCenter(SupportConversation $conversation): void
    {
        $conversation->update(['unread_center' => 0]);
    }

    public function adminUnreadTotal(): int
    {
        return (int) SupportConversation::query()->sum('unread_admin');
    }

    public function centerUnread(Center $center): int
    {
        return (int) (SupportConversation::where('center_id', $center->id)->value('unread_center') ?? 0);
    }

    protected function storeMessage(
        SupportConversation $conversation,
        Center $center,
        string $senderType,
        int $senderId,
        string $senderName,
        ?string $senderRole,
        string $body,
        bool $notifyCenter,
        bool $notifyAdmin,
    ): SupportMessage {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('Empty message');
        }

        $message = SupportMessage::create([
            'conversation_id' => $conversation->id,
            'center_id' => $center->id,
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'sender_role' => $senderRole,
            'body' => $body,
        ]);

        $conversation->update([
            'last_message' => Str::limit($body, 180),
            'last_sender_name' => $senderName,
            'last_sender_type' => $senderType,
            'last_message_at' => now(),
            'unread_admin' => $notifyAdmin ? ($conversation->unread_admin + 1) : $conversation->unread_admin,
            'unread_center' => $notifyCenter ? ($conversation->unread_center + 1) : $conversation->unread_center,
        ]);

        $payload = $message->toChatArray();
        $firebaseKey = $this->firebase->pushSupportMessage($center->id, $payload);
        if ($firebaseKey) {
            $message->update(['firebase_key' => $firebaseKey]);
            $payload['firebase_key'] = $firebaseKey;
        }

        $this->notifyParties($center, $message, $notifyCenter, $notifyAdmin);

        return $message->fresh();
    }

    protected function notifyParties(Center $center, SupportMessage $message, bool $notifyCenter, bool $notifyAdmin): void
    {
        $preview = Str::limit($message->body, 120);
        $titleAr = 'رسالة دعم جديدة';
        $titleEn = 'New support message';

        try {
            if ($notifyCenter) {
                $this->notifications->sendAdminNotification([
                    'target_type' => 'centers',
                    'recipients' => [(string) $center->id],
                    'send_push' => true,
                    'type' => 'support_chat',
                    'ar' => [
                        'title' => $titleAr,
                        'text' => $message->sender_name . ': ' . $preview,
                    ],
                    'en' => [
                        'title' => $titleEn,
                        'text' => $message->sender_name . ': ' . $preview,
                    ],
                ]);
            }

            if ($notifyAdmin) {
                // Push to all admins with FCM tokens
                $tokens = Admin::query()->with('fcmTokens')->get()
                    ->flatMap(fn ($a) => $a->fcmTokens->pluck('token'))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($tokens)) {
                    $this->notifications->sendMulticast(
                        $titleEn . ' · ' . ($center->name ?? 'Center'),
                        $message->sender_name . ': ' . $preview,
                        $tokens,
                        [
                            'action' => 'support_chat',
                            'center_id' => (string) $center->id,
                            'message_id' => (string) $message->id,
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Support chat notify failed: ' . $e->getMessage());
        }
    }

    public static function resolveActiveCenter(): ?Center
    {
        $currentDb = Config::get('database.connections.mysql.database');

        return Center::where('database', $currentDb)->first()
            ?? (session('active_center_domain')
                ? Center::where('domain', session('active_center_domain'))->first()
                : null);
    }
}
