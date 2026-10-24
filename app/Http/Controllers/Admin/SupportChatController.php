<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Services\FirebaseRealtimeService;
use App\Services\SupportAccessService;
use App\Services\SupportChatService;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function index(SupportChatService $chat, FirebaseRealtimeService $firebase)
    {
        $conversations = $chat->listConversationsForAdmin();
        $firebaseConfig = $firebase->webConfig();
        $customToken = null;
        $admin = auth('admin')->user();
        if ($firebaseConfig && $admin) {
            $customToken = $firebase->createCustomToken('admin_' . $admin->id, [
                'role' => 'admin',
                'name' => $admin->name,
            ]);
        }

        return view('Admin.SubViews.SupportChat.index', [
            'title' => __('locale.support_chat'),
            'conversations' => $conversations,
            'firebaseConfig' => $firebaseConfig,
            'firebaseToken' => $customToken,
            'viewer' => [
                'type' => 'admin',
                'id' => $admin->id,
                'name' => $admin->name,
            ],
            'unreadTotal' => $chat->adminUnreadTotal(),
            'limits' => [
                'image_mb' => (int) (SupportChatService::IMAGE_MAX_KB / 1024),
                'video_mb' => (int) (SupportChatService::VIDEO_MAX_KB / 1024),
            ],
        ]);
    }

    public function messages(Request $request, int $centerId, SupportChatService $chat)
    {
        $center = Center::findOrFail($centerId);
        $conversation = $chat->ensureConversation($center);
        $chat->markReadForAdmin($conversation);
        $afterId = (int) $request->query('after_id', 0);
        $messages = $chat->messages($conversation, $afterId);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'center' => [
                'id' => $center->id,
                'name' => $center->name,
                'domain' => $center->domain,
                'support_chat_enabled' => (bool) ($center->support_chat_enabled ?? true),
            ],
            'messages' => $messages,
            'unread_total' => $chat->adminUnreadTotal(),
        ]);
    }

    public function send(Request $request, int $centerId, SupportChatService $chat)
    {
        $request->validate([
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:' . SupportChatService::VIDEO_MAX_KB,
        ]);

        $center = Center::findOrFail($centerId);

        try {
            $message = $chat->sendFromAdmin(
                auth('admin')->user(),
                $center,
                $request->input('body'),
                $request->file('attachment')
            );
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $message->toChatArray(),
        ]);
    }

    public function typing(Request $request, int $centerId, SupportChatService $chat)
    {
        $admin = auth('admin')->user();
        $isTyping = (bool) $request->boolean('typing', true);
        if ($isTyping) {
            $chat->setTyping($centerId, [
                'sender_type' => 'admin',
                'sender_id' => $admin->id,
                'sender_name' => $admin->name,
            ]);
        } else {
            app(FirebaseRealtimeService::class)->clearTyping($centerId, 'admin_' . $admin->id);
        }

        return response()->json(['success' => true]);
    }

    public function toggleCenter(Request $request, int $centerId, SupportAccessService $access)
    {
        $request->validate([
            'support_chat_enabled' => 'sometimes|boolean',
            'support_tickets_enabled' => 'sometimes|boolean',
        ]);

        $center = Center::findOrFail($centerId);
        $data = [];
        if ($request->has('support_chat_enabled')) {
            $data['support_chat_enabled'] = $request->boolean('support_chat_enabled');
        }
        if ($request->has('support_tickets_enabled')) {
            $data['support_tickets_enabled'] = $request->boolean('support_tickets_enabled');
        }
        if ($data) {
            $center->update($data);
        }

        return response()->json([
            'success' => true,
            'center' => [
                'id' => $center->id,
                'support_chat_enabled' => (bool) $center->support_chat_enabled,
                'support_tickets_enabled' => (bool) $center->support_tickets_enabled,
            ],
        ]);
    }

    public function unread(SupportChatService $chat)
    {
        return response()->json([
            'success' => true,
            'unread' => $chat->adminUnreadTotal(),
        ]);
    }
}
