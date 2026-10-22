<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Services\FirebaseRealtimeService;
use App\Services\SupportChatService;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function index(SupportChatService $chat, FirebaseRealtimeService $firebase)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $conversation = $chat->ensureConversation($center);
        $chat->markReadForCenter($conversation);
        $messages = $chat->messages($conversation);

        $user = auth('center_user')->user();
        $firebaseConfig = $firebase->webConfig();
        $customToken = null;
        if ($firebaseConfig && $user) {
            $customToken = $firebase->createCustomToken('center_' . $center->id . '_user_' . $user->id, [
                'role' => 'center_user',
                'center_id' => $center->id,
                'name' => $user->name,
            ]);
        }

        return view('CenterUser.SubViews.SupportChat.index', [
            'title' => __('locale.support_chat'),
            'center' => $center,
            'conversation' => $conversation,
            'messages' => $messages,
            'firebaseConfig' => $firebaseConfig,
            'firebaseToken' => $customToken,
            'viewer' => [
                'type' => 'center_user',
                'id' => $user->id,
                'name' => $user->name,
            ],
            'unread' => $chat->centerUnread($center),
        ]);
    }

    public function messages(Request $request, SupportChatService $chat)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $conversation = $chat->ensureConversation($center);
        $chat->markReadForCenter($conversation);
        $afterId = (int) $request->query('after_id', 0);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'messages' => $chat->messages($conversation, $afterId),
            'unread' => $chat->centerUnread($center),
        ]);
    }

    public function send(Request $request, SupportChatService $chat)
    {
        $request->validate(['body' => 'required|string|max:5000']);
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $message = $chat->sendFromCenterUser(auth('center_user')->user(), $center, $request->input('body'));

        return response()->json([
            'success' => true,
            'message' => $message->toChatArray(),
        ]);
    }

    public function unread(SupportChatService $chat)
    {
        $center = SupportChatService::resolveActiveCenter();
        return response()->json([
            'success' => true,
            'unread' => $center ? $chat->centerUnread($center) : 0,
        ]);
    }
}
