<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\NotificationDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FirebaseSettingRequest;
use App\Http\Requests\Admin\NotificationRequest;
use App\Models\AppUser;
use App\Models\Center;
use App\Services\AppNotificationService;
use App\Services\FirebaseSettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function index(NotificationDataTable $dataTable, FirebaseSettingService $firebaseSettingService)
    {
        if (!auth('admin')->user()->can('VIEW_NOTIFICATIONS')) {
            return abort(403);
        }

        $title = __('locale.notifications');
        $users = AppUser::query()->where('is_active', 1)->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email', 'phone']);
        $centers = Center::query()->where('status', 'approve')->orderBy('name')->get(['id', 'name', 'domain']);
        $firebase = $firebaseSettingService->get();
        $sendUrl = route('admin.notifications.send');
        $firebaseUrl = route('admin.notifications.firebase.save');

        return $dataTable->render('Admin.SubViews.Notification.index', compact(
            'title',
            'users',
            'centers',
            'firebase',
            'sendUrl',
            'firebaseUrl'
        ));
    }

    public function send(NotificationRequest $request, AppNotificationService $service)
    {
        if (!auth('admin')->user()->can('CREATE_NOTIFICATIONS')) {
            return abort(403);
        }

        $item = $service->sendAdminNotification($request->validated() + [
            'image' => $request->file('image'),
            'ar' => $request->input('ar'),
            'en' => $request->input('en'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        if ($item) {
            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_CREATED, [
                'id' => $item->id,
                'reload' => true,
            ]);
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function resend(Request $request, AppNotificationService $service)
    {
        if (!auth('admin')->user()->can('CREATE_NOTIFICATIONS')) {
            return abort(403);
        }

        $ok = $service->resend((int) $request->id);
        if ($ok) {
            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_OK);
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function toggleStatus(Request $request, AppNotificationService $service)
    {
        if (!auth('admin')->user()->can('UPDATE_NOTIFICATIONS')) {
            return abort(403);
        }

        $item = $service->toggleStatus((int) $request->id);
        if ($item) {
            return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_OK, $item);
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function saveFirebase(FirebaseSettingRequest $request, FirebaseSettingService $service)
    {
        if (!auth('admin')->user()->can('UPDATE_FIREBASE_SETTINGS')) {
            return abort(403);
        }

        $item = $service->save($request->validated());
        return MyHelper::responseJSON(__('admin.operation_done_successfully'), Response::HTTP_OK, $item);
    }
}
