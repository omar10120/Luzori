<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceSettingRequest;
use App\Services\InvoiceSettingsService;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class InvoiceSettingController extends Controller
{
    private InvoiceSettingsService $invoiceSettingsService;
    private string $model = 'InvoiceSettings';
    private string $plural = 'invoice_settings';
    private string $indexRoute;
    private string $updateOrCreateRoute;

    public function __construct(InvoiceSettingsService $invoiceSettingsService)
    {
        $this->invoiceSettingsService = $invoiceSettingsService;
        $this->indexRoute = 'center_user.' . $this->plural . '.index';
        $this->updateOrCreateRoute = 'center_user.' . $this->plural . '.updateOrCreate';
    }

    public function index(Request $request)
    {
        $can = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $menu = __('locale.settings');
        $menu_link = route('center_user.mainsettings.index');
        $item = $this->invoiceSettingsService->first();
        $title = __('locale.InvoiceSettings');
        $requestUrl = route($this->updateOrCreateRoute);
        $center = $this->resolveActiveCenter();
        $smsPackageAmount = (int) ($center?->sms_request_package ?? 0);
        $smsPackages = PaymentController::smsPackages();

        $view = 'CenterUser.SubViews.' . $this->model . '.index';

        return view($view, compact('item', 'requestUrl', 'title', 'menu', 'menu_link', 'smsPackageAmount', 'smsPackages'));
    }

    public function updateOrCreate(InvoiceSettingRequest $request)
    {
        $can = 'UPDATE_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($can, 'center_api')) {
            return abort(403);
        }

        $data = $request->validated();
        if ($data['sms_allow'] && !$this->hasSmsPackage()) {
            session(['pending_invoice_settings' => $data]);

            return response()->json([
                'success' => false,
                'requires_sms_payment' => true,
                'message' => __('general.sms_package_required') ?? 'Please purchase an SMS package to enable SMS notifications.',
                'create_session_url' => route('center_user.subscription.sms.create-session'),
                'callback_url' => route('center_user.subscription.sms.callback'),
            ], 402);
        }

        $item = $this->invoiceSettingsService->update($data);

        if ($item) {
            return MyHelper::responseJSON(
                'redirect_to_home',
                Response::HTTP_CREATED,
                route('center_user.invoice_settings.index')
            );
        }

        return MyHelper::responseJSON(__('admin.an_error_occurred'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Download a Windows .bat that opens Chrome in silent-print (cashier) mode.
     */
    public function downloadCashierChrome()
    {
        $url = url('/');
        $content = <<<BAT
@echo off
REM Luzori Cashier Mode — silent print to default printer (no dialog)
set "CHROME="
if exist "%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe" set "CHROME=%ProgramFiles%\\Google\\Chrome\\Application\\chrome.exe"
if exist "%ProgramFiles(x86)%\\Google\\Chrome\\Application\\chrome.exe" set "CHROME=%ProgramFiles(x86)%\\Google\\Chrome\\Application\\chrome.exe"
if exist "%LocalAppData%\\Google\\Chrome\\Application\\chrome.exe" set "CHROME=%LocalAppData%\\Google\\Chrome\\Application\\chrome.exe"
if "%CHROME%"=="" (
  echo Google Chrome not found. Please install Chrome.
  pause
  exit /b 1
)
start "" "%CHROME%" --kiosk-printing --disable-print-preview "{$url}"
BAT;

        return response($content, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="luzori-cashier-chrome.bat"',
        ]);
    }

    private function hasSmsPackage(): bool
    {
        return (int) ($this->resolveActiveCenter()?->sms_request_package ?? 0) > 0;
    }

    private function resolveActiveCenter(): ?Center
    {
        $domain = session('active_center_domain');
        if (!$domain && in_array(request()->getHost(), ['127.0.0.1', 'localhost'], true)) {
            $domain = 'center';
        }

        return $domain ? Center::where('domain', $domain)->first() : null;
    }
}
