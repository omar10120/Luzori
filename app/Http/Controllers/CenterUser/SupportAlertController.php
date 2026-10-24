<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Services\SupportAccessService;
use App\Services\SupportChatService;

class SupportAlertController extends Controller
{
    public function poll(SupportChatService $chat, SupportAccessService $access)
    {
        $center = SupportChatService::resolveActiveCenter();
        $user = auth('center_user')->user();

        if (!$center) {
            return response()->json([
                'success' => true,
                'chat_unread' => 0,
                'ticket_open' => 0,
                'chat_fp' => null,
                'ticket_fp' => null,
                'latest_chat' => null,
                'latest_ticket' => null,
            ]);
        }

        $canChat = $access->canCenterUserUseChat($center, $user);
        $canTickets = $access->canCenterUserUseTickets($center, $user);

        $chatUnread = $canChat ? $chat->centerUnread($center) : 0;

        $latestMsg = null;
        if ($canChat) {
            $latestMsg = SupportMessage::query()
                ->where('center_id', $center->id)
                ->where('sender_type', 'admin')
                ->orderByDesc('id')
                ->first();
        }

        $latestTicket = null;
        $openTickets = 0;
        if ($canTickets) {
            $latestTicket = SupportTicket::query()
                ->where('center_id', $center->id)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
            $openTickets = SupportTicket::query()
                ->where('center_id', $center->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();
        }

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
                'body' => $latestMsg->sender_name . ': ' .
                    ($latestMsg->body ?: ($latestMsg->attachment_type === 'video' ? 'Video' : 'Image')),
                'url' => route('center_user.support-chat.index'),
                'at' => optional($latestMsg->created_at)->toIso8601String(),
            ] : null,
            'latest_ticket' => $latestTicket ? [
                'id' => $latestTicket->id,
                'title' => __('locale.support_ticket') . ' #' . $latestTicket->id,
                'body' => $latestTicket->subject,
                'url' => route('center_user.support-tickets.show', $latestTicket->id),
                'at' => optional($latestTicket->updated_at)->toIso8601String(),
            ] : null,
        ]);
    }
}
