<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\SupportConversation;
use App\Services\FirebaseRealtimeService;
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
            ],
            'messages' => $messages,
            'unread_total' => $chat->adminUnreadTotal(),
        ]);
    }

    public function send(Request $request, int $centerId, SupportChatService $chat)
    {
        $request->validate(['body' => 'required|string|max:5000']);
        $center = Center::findOrFail($centerId);
        $message = $chat->sendFromAdmin(auth('admin')->user(), $center, $request->input('body'));

        return response()->json([
            'success' => true,
            'message' => $message->toChatArray(),
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
