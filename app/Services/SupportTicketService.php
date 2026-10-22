<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Center;
use App\Models\CenterUser;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupportTicketService
{
    public function __construct(
        protected AppNotificationService $notifications,
    ) {}

    public function createFromCenter(Center $center, CenterUser $user, array $data): SupportTicket
    {
        $ticket = SupportTicket::create([
            'center_id' => $center->id,
            'opened_by_id' => $user->id,
            'opened_by_name' => $user->name,
            'subject' => trim($data['subject']),
            'body' => trim($data['body']),
            'status' => 'open',
            'priority' => $data['priority'] ?? 'normal',
            'last_reply_at' => now(),
        ]);

        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'center_user',
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'body' => $ticket->body,
        ]);

        $this->notifyAdminsNewTicket($center, $ticket);

        return $ticket;
    }

    public function replyFromAdmin(SupportTicket $ticket, Admin $admin, string $body, ?string $status = null): SupportTicketReply
    {
        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'sender_name' => $admin->name,
            'body' => trim($body),
        ]);

        $ticket->update([
            'last_reply_at' => now(),
            'status' => $status ?: ($ticket->status === 'open' ? 'in_progress' : $ticket->status),
        ]);

        $this->notifyCenterTicketReply($ticket, $reply);

        return $reply;
    }

    public function replyFromCenter(SupportTicket $ticket, CenterUser $user, string $body): SupportTicketReply
    {
        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'center_user',
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'body' => trim($body),
        ]);

        $ticket->update([
            'last_reply_at' => now(),
            'status' => $ticket->status === 'resolved' ? 'open' : $ticket->status,
        ]);

        $this->notifyAdminsTicketReply($ticket, $reply);

        return $reply;
    }

    public function updateStatus(SupportTicket $ticket, string $status): SupportTicket
    {
        $ticket->update(['status' => $status]);
        return $ticket->fresh();
    }

    protected function notifyAdminsNewTicket(Center $center, SupportTicket $ticket): void
    {
        try {
            $tokens = Admin::query()->with('fcmTokens')->get()
                ->flatMap(fn ($a) => $a->fcmTokens->pluck('token'))
                ->filter()->unique()->values()->all();

            $title = 'New support ticket';
            $body = ($center->name ?? 'Center') . ': ' . $ticket->subject;

            if (!empty($tokens)) {
                $this->notifications->sendMulticast($title, $body, $tokens, [
                    'action' => 'support_ticket',
                    'ticket_id' => (string) $ticket->id,
                ]);
            }

            // Also create in-app notification for center history consistency (optional skip)
        } catch (\Throwable $e) {
            Log::warning('Ticket notify admins failed: ' . $e->getMessage());
        }
    }

    protected function notifyCenterTicketReply(SupportTicket $ticket, SupportTicketReply $reply): void
    {
        try {
            $this->notifications->sendAdminNotification([
                'target_type' => 'centers',
                'recipients' => [(string) $ticket->center_id],
                'send_push' => true,
                'type' => 'support_ticket',
                'ar' => [
                    'title' => 'رد على تذكرة الدعم',
                    'text' => $reply->sender_name . ': ' . Str::limit($reply->body, 120),
                ],
                'en' => [
                    'title' => 'Support ticket reply',
                    'text' => $reply->sender_name . ': ' . Str::limit($reply->body, 120),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Ticket notify center failed: ' . $e->getMessage());
        }
    }

    protected function notifyAdminsTicketReply(SupportTicket $ticket, SupportTicketReply $reply): void
    {
        try {
            $centerName = $ticket->center?->name ?? 'Center';
            $tokens = Admin::query()->with('fcmTokens')->get()
                ->flatMap(fn ($a) => $a->fcmTokens->pluck('token'))
                ->filter()->unique()->values()->all();

            if (!empty($tokens)) {
                $this->notifications->sendMulticast(
                    'Ticket reply · ' . $centerName,
                    $reply->sender_name . ': ' . Str::limit($reply->body, 120),
                    $tokens,
                    [
                        'action' => 'support_ticket',
                        'ticket_id' => (string) $ticket->id,
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Ticket reply notify admins failed: ' . $e->getMessage());
        }
    }
}
