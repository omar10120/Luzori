<?php

namespace App\Http\Controllers\CenterUser;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CenterController extends Controller
{
    private function resolveActiveCenter(): ?Center
    {
        $host = request()->getHost();

        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            return Center::where('domain', 'center4')->first();
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
        $menu_link = route('center_user.settings.index');
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
        ]);

        $center->update($validated);

        return MyHelper::responseJSON(
            __('admin.operation_done_successfully'),
            Response::HTTP_OK,
            route('center_user.center.index')
        );
    }
}
