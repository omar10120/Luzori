<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\FirebaseRealtimeService;
use App\Services\SupportAccessService;
use App\Services\SupportChatService;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function index(SupportChatService $chat, FirebaseRealtimeService $firebase, SupportAccessService $access)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $user = auth('center_user')->user();
        if (!$access->canCenterUserUseChat($center, $user)) {
            return view('CenterUser.SubViews.SupportChat.disabled', [
                'title' => __('locale.support_chat'),
                'center' => $center,
            ]);
        }

        $conversation = $chat->ensureConversation($center);
        $chat->markReadForCenter($conversation);
        $messages = $chat->messages($conversation);

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
            'limits' => [
                'image_mb' => (int) (SupportChatService::IMAGE_MAX_KB / 1024),
                'video_mb' => (int) (SupportChatService::VIDEO_MAX_KB / 1024),
            ],
        ]);
    }

    public function messages(Request $request, SupportChatService $chat, SupportAccessService $access)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);
        $user = auth('center_user')->user();
        if (!$access->canCenterUserUseChat($center, $user)) {
            return response()->json(['success' => false, 'message' => __('field.support_chat_disabled')], 403);
        }

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

    public function send(Request $request, SupportChatService $chat, SupportAccessService $access)
    {
        $request->validate([
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:' . SupportChatService::VIDEO_MAX_KB,
        ]);

        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        try {
            $message = $chat->sendFromCenterUser(
                auth('center_user')->user(),
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

    public function typing(Request $request, SupportChatService $chat, SupportAccessService $access)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);
        $user = auth('center_user')->user();
        if (!$access->canCenterUserUseChat($center, $user)) {
            return response()->json(['success' => false], 403);
        }

        $isTyping = (bool) $request->boolean('typing', true);
        if ($isTyping) {
            $chat->setTyping($center->id, [
                'sender_type' => 'center_user',
                'sender_id' => $user->id,
                'sender_name' => $user->name,
            ]);
        } else {
            app(FirebaseRealtimeService::class)->clearTyping($center->id, 'center_user_' . $user->id);
        }

        return response()->json(['success' => true]);
    }

    public function unread(SupportChatService $chat, SupportAccessService $access)
    {
        $center = SupportChatService::resolveActiveCenter();
        $user = auth('center_user')->user();
        if (!$center || !$access->canCenterUserUseChat($center, $user)) {
            return response()->json(['success' => true, 'unread' => 0]);
        }

        return response()->json([
            'success' => true,
            'unread' => $chat->centerUnread($center),
        ]);
    }

    /** Center management: toggle support per branch */
    public function settings(SupportAccessService $access)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $branches = Branch::query()->with('translations')->orderBy('id')->get();

        return view('CenterUser.SubViews.SupportChat.settings', [
            'title' => __('locale.support_settings'),
            'center' => $center,
            'branches' => $branches,
            'centerChatEnabled' => $access->centerChatEnabled($center),
            'centerTicketsEnabled' => $access->centerTicketsEnabled($center),
        ]);
    }

    public function toggleBranch(Request $request, int $branchId, SupportAccessService $access)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        if (!$access->centerChatEnabled($center) && !$access->centerTicketsEnabled($center)) {
            return response()->json(['success' => false, 'message' => __('field.support_disabled_by_admin')], 403);
        }

        $request->validate([
            'support_chat_enabled' => 'sometimes|boolean',
            'support_tickets_enabled' => 'sometimes|boolean',
        ]);

        $branch = Branch::findOrFail($branchId);
        $data = [];
        if ($request->has('support_chat_enabled')) {
            $data['support_chat_enabled'] = $request->boolean('support_chat_enabled');
        }
        if ($request->has('support_tickets_enabled')) {
            $data['support_tickets_enabled'] = $request->boolean('support_tickets_enabled');
        }
        if ($data) {
            $branch->update($data);
        }

        return response()->json([
            'success' => true,
            'branch' => [
                'id' => $branch->id,
                'support_chat_enabled' => (bool) $branch->support_chat_enabled,
                'support_tickets_enabled' => (bool) $branch->support_tickets_enabled,
            ],
        ]);
    }
}
