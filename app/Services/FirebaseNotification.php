<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as MessagingNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseNotification
{
    function sendUsersNotification($request)
    {
        $users = $request['users'];
        $topic = false;
        if (in_array('all', $users)) {
            $users = User::pluck('id');
            $topic = true;
        }

        if (count($users) > 0) {
            DB::beginTransaction();
            $notification = $this->create_user_notification($request, $users);
            if ($topic) {
                $this->sendToTopic($request, 'users');
            } else {
                foreach ($users as $user) {
                    $user_info = User::find($user);
                    $this->sendToOne($request['title_' . $user_info->language->name], $request['text_' . $user_info->language->name], $user_info->fcmTokens()->pluck('token')->toArray());
                }
            }
            DB::commit();
            return $notification;
        } else {
            return null;
        }
    }

    function sendUserNotification($request, $user_id)
    {
        DB::beginTransaction();
        $user = User::find($user_id);
        $notification = $this->create_user_notification($request, $user_id);
        if ($notification) {
            $this->sendToOne($request[$user->language->name]['title'], $request[$user->language->name]['text'], $user->fcmTokens()->pluck('token')->toArray());
            DB::commit();
            return $notification;
        } else {
            return null;
        }
    }

    function create_user_notification($request, $users)
    {
        DB::beginTransaction();
        $notification = Notification::create($request);
        if ($notification) {
            if (is_numeric($users)) {
                $user = User::find($users);
                $user->notifications()->attach($notification);
            } else {
                foreach ($users as $user_id) {
                    $user = User::find($user_id);
                    $user->notifications()->attach($notification);
                }
            }
        }
        DB::commit();
        return $notification;
    }

    public static function sendToTopic($request, $topic)
    {
        $messaging = Firebase::messaging();
        $notification = MessagingNotification::fromArray([
            'title' => $request['ar']['title'],
            'body' => $request['ar']['text'],
        ]);

        $message = CloudMessage::new ();
        $message = $message->withTarget('topic', $topic);
        $message = $message->withNotification($notification);
        return $messaging->send($message);
    }

    public static function sendToOne($title, $body, $tokens, $extra = null)
    {
        if ($tokens) {
            $messaging = Firebase::messaging();
            $notification = MessagingNotification::fromArray([
                'title' => $title,
                'body' => $body,
            ]);

            $message = CloudMessage::new ();
            $message = $message->withNotification($notification);

            if ($extra) {
                $message = $message->withData($extra);
            } else {
                $message = $message->withData([
                    'action' => 'info',
                ]);
            }

            $validTokens = $messaging->validateRegistrationTokens($tokens)['valid'];
            if (count($validTokens) > 0) {
                return $messaging->sendMulticast($message, $validTokens);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
