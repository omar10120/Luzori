<?php

namespace App\Services;

use App\Models\Center;
use App\Models\CenterUser;
use App\Models\User;
use App\Services\FirebaseNotification;

class NotificationService
{
    protected $firebaseNotification;
    public function __construct()
    {
        $this->firebaseNotification = new FirebaseNotification();
    }

    public function getByUser($user_id)
    {
        $user = User::find($user_id);
        $userNotifications = $user->notifications()->orderByDesc('id')->paginate(8)->items();
        foreach ($userNotifications as $userNotification) {
            $userNotification->users()->updateExistingPivot($user, ['is_read' => 1]);
        }
        return $userNotifications;
    }

    public function getByCenter($center_id)
    {
        $center = Center::find($center_id);
        $centerNotifications = $center->notifications()->orderByDesc('id')->paginate(8)->items();
        foreach ($centerNotifications as $centerNotification) {
            $centerNotification->centers()->updateExistingPivot($center, ['is_read' => 1]);
        }
        return $centerNotifications;
    }

    public function getByCenterUser($center_user_id)
    {
        $centeruser = CenterUser::find($center_user_id);
        $centerNotifications = $centeruser->notifications()->orderByDesc('id')->paginate(8)->items();
        foreach ($centerNotifications as $centerNotification) {
            $centerNotification->centers()->updateExistingPivot($centeruser, ['is_read' => 1]);
        }
        return $centerNotifications;
    }

    public function add($request)
    {
        $notification = $this->firebaseNotification->sendUsersNotification($request);
        if ($notification) {
            return true;
        } else {
            return false;
        }
    }
}
