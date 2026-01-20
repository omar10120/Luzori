<?php

namespace App\Http\Middleware;

use App\Helpers\MyHelper;
use App\Models\Center;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SetActiveCenter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $domain = $request->header('domain');
        if ($domain) {
            $center = Center::where('domain', $domain)->first();
            if ($center) {
                Config::set('database.connections.mysql.database', $center->database);
                DB::reconnect();
                return $next($request);
            }
        }
        return MyHelper::responseJSON(__('api.domain_dont_exists'), Response::HTTP_BAD_REQUEST);
    }
}
