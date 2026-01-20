<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function swap(Request $request)
    {
        if (!in_array($request->locale, config("translatable.locales"))) {
            abort(400);
        }

        session(['locale' => $request->locale]);
        return redirect()->back();
    }
}
