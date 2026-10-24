<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\SupportChatService;

class SupportAlertController extends Controller
{
    public function poll(SupportChatService $chat)
    {
        $chatUnread = $chat->adminUnreadTotal();

        $latestMsg = SupportMessage::query()
            ->with('center:id,name,domain')
            ->where('sender_type', 'center_user')
            ->orderByDesc('id')
            ->first();

        $latestTicket = SupportTicket::query()
            ->with('center:id,name,domain')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        $openTickets = SupportTicket::query()
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        return response()->json([
            'success' => true,
            'chat_unread' => $chatUnread,
            'ticket_open' => $openTickets,
            'chat_fp' => $latestMsg ? ('m' . $latestMsg->id) : null,
            'ticket_fp' => $latestTicket
                ? ('t' . $latestTicket->id . '_' . optional($latestTicket->updated_at)->timestamp)
                : null,
            'latest_chat' => $latestMsg ? [
                'id' => $latestMsg->id,
                'title' => __('locale.support_chat'),
                'body' => ($latestMsg->center->name ?? '') . ' · ' . $latestMsg->sender_name . ': ' .
                    ($latestMsg->body ?: ($latestMsg->attachment_type === 'video' ? 'Video' : 'Image')),
                'url' => route('admin.support-chat.index'),
                'at' => optional($latestMsg->created_at)->toIso8601String(),
            ] : null,
            'latest_ticket' => $latestTicket ? [
                'id' => $latestTicket->id,
                'title' => __('locale.support_ticket') . ' #' . $latestTicket->id,
                'body' => ($latestTicket->center->name ?? '') . ' · ' . $latestTicket->subject,
                'url' => route('admin.support-tickets.show', $latestTicket->id),
                'at' => optional($latestTicket->updated_at)->toIso8601String(),
            ] : null,
        ]);
    }
}
