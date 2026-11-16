<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Services\GoogleDriveBackupService;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    private SettingService $settingService;
    private $model = 'Setting';
    private $plural = 'settings';
    private $indexRoute;
    private $updateOrCreateRoute;

    /**
     * @param SettingService $settingService
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
        $this->indexRoute = 'admin.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'admin.' . $this->plural . '.updateOrCreate';
    }

    public function index(Request $request)
    {
        $menu = __('locale.' . $this->plural);
        $menu_link = route($this->indexRoute);

        $item = $this->settingService->all();
        $title = __('general.edit');
        $requestUrl = route($this->updateOrCreateRoute, ['id' => $request->id]);

        $view = 'Admin.SubViews.' . $this->model . '.index';
        $drive = app(GoogleDriveBackupService::class);
        $driveClientId = $drive->clientId();
        $driveHasSecret = $drive->hasSecret();
        $driveConnected = $drive->connected();
        $driveRedirect = $drive->redirectUri();

        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link', 'driveClientId', 'driveHasSecret', 'driveConnected', 'driveRedirect'));
    }

    public function updateOrCreate(SettingRequest $request)
    {
        $newRequest = $request->only(
            'ar',
            'en',
            'language',
            'tips',
            'image',
        );
        $item = $this->settingService->edit($newRequest);
        if ($item) {
            return MyHelper::responseJSON('redirect_to_home', Response::HTTP_CREATED, route('admin.settings.index'));
        } else {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveDrive(Request $request, GoogleDriveBackupService $drive)
    {
        $request->validate([
            'google_drive_client_id' => 'required|string|max:500',
            'google_drive_client_secret' => 'nullable|string|max:500',
        ]);

        $drive->saveCredentials(
            $request->input('google_drive_client_id'),
            $request->input('google_drive_client_secret')
        );

        return redirect()->route($this->indexRoute)->with('success', __('admin.operation_done_successfully'));
    }

    public function connectDrive(GoogleDriveBackupService $drive)
    {
        if ($drive->clientId() === '' || !$drive->hasSecret()) {
            return redirect()->route($this->indexRoute)->with('error', __('field.drive_credentials_required'));
        }

        $state = Str::random(40);
        session(['google_drive_oauth_state' => $state]);

        return redirect()->away($drive->authorizationUrl($state));
    }

    public function connectDriveCallback(Request $request, GoogleDriveBackupService $drive)
    {
        if (!$request->filled('state') || $request->input('state') !== session('google_drive_oauth_state')) {
            abort(403);
        }

        session()->forget('google_drive_oauth_state');

        if ($request->filled('error') || !$request->filled('code')) {
            return redirect()->route($this->indexRoute)->with('error', __('field.drive_connect_failed'));
        }

        try {
            $drive->exchangeCode($request->input('code'));
        } catch (\Throwable $e) {
            return redirect()->route($this->indexRoute)->with('error', $e->getMessage());
        }

        return redirect()->route($this->indexRoute)->with('success', __('field.drive_connected'));
    }
}
