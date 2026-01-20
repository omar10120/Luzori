<?php

namespace App\Http\Middleware;

use App\Models\Center;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


class SetActiveCenterCp
{
    public function handle(Request $request, Closure $next)
{
    $host = $request->getHost(); // e.g., www.luzori.com or 127.0.0.1
    $parts = explode('.', $host);
    $subdomain = null;

    if (in_array($host, ['127.0.0.1', 'localhost'])) {
        // 🔹 Local dev: set a default test center
        $center = Center::where('domain', 'center')->first(); // or specify manually
        if ($center) {
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');
        }

        return $next($request);
    }

    if (count($parts) > 2 && $parts[0] !== 'www') {
        $subdomain = $parts[0];
    } elseif (count($parts) > 3 && $parts[0] === 'www') {
        $subdomain = $parts[1];
    }

    if ($subdomain) {
        $center = Center::where('domain', $subdomain)->first();

        if ($center) {
            Config::set('database.connections.mysql.database', $center->database);
            DB::purge('mysql');
            DB::reconnect('mysql');
            return $next($request);
        }

        return abort(404, 'Center not found');
    }

    return $next($request);
}

}
