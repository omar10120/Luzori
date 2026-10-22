<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\NotifiesCenter; 

class CenterController extends Controller
{
    use NotifiesCenter;      
    private function resolveActiveCenter(): ?Center
    {
        $host = request()->getHost();

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return Center::where('domain', 'center35')->first();
        }

        $domain = session('active_center_domain');

        if ($domain) {
            return Center::where('domain', $domain)->first();
        }

        $parts     = explode('.', $host);
        $subdomain = count($parts) > 2 && $parts[0] !== 'www' ? $parts[0] : null;

        return $subdomain ? Center::where('domain', $subdomain)->first() : null;
    }

    public function index()
    {
        if (!auth('center_user')->user()->can('VIEW_SETTINGS', 'center_api')) {
            return abort(403);
        }

        $center = $this->resolveActiveCenter();
        if (!$center) {
            abort(404, 'Center not found.');
        }

        $title = __('locale.center_info');
        $menu = __('locale.settings');
        $menu_link = route('center_user.mainsettings.index');
        $requestUrl = route('center_user.center.update');

        return view('CenterUser.SubViews.Center.index', compact('center', 'title', 'menu', 'menu_link', 'requestUrl'));
    }

    public function update(Request $request)
    {
        if (!auth('center_user')->user()->can('UPDATE_SETTINGS', 'center_api')) {
            return abort(403);
        }
    
        $center = $this->resolveActiveCenter();
        if (!$center) {
            return MyHelper::responseJSON('Center not found.', Response::HTTP_NOT_FOUND);
        }
    
        $validated = $request->validate([
            'iban'                  => 'required|string|max:100',
            'BankAccountHolderName' => 'required|string|max:100',
            'BusinessName'          => 'required|string|max:100',
            'BankAccount'           => 'required|string|max:100',
            'bank_name'             => 'required|string|max:100',
            'BankId'                => 'required|string|max:100',
        ]);
    
        // ---- Snapshot BEFORE update so we can diff & notify ----
        $oldValues = [];
        foreach (array_keys($validated) as $field) {
            $oldValues[$field] = (string) ($center->{$field} ?? '');
        }
    
        $center->update($validated);
    
        // ---- Compute which fields actually changed ----
        $changedFields = [];
        foreach ($validated as $field => $newValue) {
            if ($oldValues[$field] !== (string) $newValue) {
                $changedFields[] = $field;
            }
        }
    
        // ---- Notify the center (best-effort, never blocks the response) ----
        if (!empty($changedFields)) {
            $this->notifyCenterBankInfoUpdated($center->fresh(), $changedFields);
        }
    
        return MyHelper::responseJSON(
            __('admin.operation_done_successfully'),
            Response::HTTP_OK,
            route('center_user.center.index')
        );
    }

    private function notifyCenterBankInfoUpdated(Center $center, array $changedFields): void
    {
        try {
            if (empty($changedFields)) {
                return;
            }

            // Human-readable labels (used in the message, never the values).
            $labels = [
                'iban'                  => ['en' => 'IBAN',              'ar' => 'رقم الآيبان'],
                'BankAccountHolderName' => ['en' => 'account holder',    'ar' => 'اسم صاحب الحساب'],
                'BusinessName'          => ['en' => 'business name',     'ar' => 'اسم النشاط التجاري'],
                'BankAccount'           => ['en' => 'bank account',      'ar' => 'رقم الحساب البنكي'],
                'bank_name'             => ['en' => 'bank name',         'ar' => 'اسم البنك'],
                'BankId'                => ['en' => 'bank ID',           'ar' => 'معرف البنك'],
            ];

            $enParts = [];
            $arParts = [];
            foreach ($changedFields as $field) {
                if (isset($labels[$field])) {
                    $enParts[] = $labels[$field]['en'];
                    $arParts[] = $labels[$field]['ar'];
                }
            }

            if (empty($enParts)) {
                return;
            }

            $enList = implode(', ', $enParts);
            $arList = implode('، ', $arParts);

            $this->notifyCenter(
                'settings',
                [
                    'en' => [
                        'title' => 'Center Bank Information Updated',
                        'text'  => 'Your center bank information was updated (fields: ' . $enList . ').',
                    ],
                    'ar' => [
                        'title' => 'تم تحديث بيانات البنك للمركز',
                        'text'  => 'تم تحديث بيانات البنك الخاصة بمركزك (الحقول: ' . $arList . ').',
                    ],
                ],
                [
                    'action'         => 'center_bank_info_updated',
                    'center_id'      => (string) $center->id,
                    'changed_fields' => implode(',', $changedFields),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('notifyCenterBankInfoUpdated failed', [
                'center_id' => $center->id ?? null,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
